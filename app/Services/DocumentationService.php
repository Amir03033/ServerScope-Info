<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;

/**
 * Markdown tutorials in content/docs/{locale}/{slug}.md.
 *
 * Each file may start with a small front matter block:
 *
 *     ---
 *     order: 2
 *     category: monitoring
 *     icon: server
 *     description: One sentence for the index and meta description.
 *     ---
 *
 * Categories (see CATEGORIES) group the sidebar and the index.
 *
 * The placeholder {app_url} is replaced with the ServerScope application
 * URL, so links into the app follow config('serverscope.app_url').
 * GitHub-style alerts (> [!NOTE], > [!TIP], > [!WARNING], > [!IMPORTANT])
 * are rendered as callouts.
 */
class DocumentationService
{
    private const CALLOUTS = [
        'nl' => ['NOTE' => 'Opmerking', 'TIP' => 'Tip', 'IMPORTANT' => 'Belangrijk', 'WARNING' => 'Let op', 'CAUTION' => 'Pas op'],
        'en' => ['NOTE' => 'Note', 'TIP' => 'Tip', 'IMPORTANT' => 'Important', 'WARNING' => 'Warning', 'CAUTION' => 'Caution'],
    ];

    /** Sidebar/index groups in display order; labels live in lang/{locale}/site.php (docs.categories). */
    public const CATEGORIES = ['start', 'monitoring', 'incidents', 'integrations', 'developers', 'account'];

    /** Old slugs that moved, so existing links keep working. */
    public const REDIRECTS = ['api-webhooks' => 'api-reference'];

    /** @var array<string, array<int, array>> */
    private array $cache = [];

    private function directory(string $locale): string
    {
        abort_unless(in_array($locale, ['nl', 'en'], true), 404);

        return base_path("content/docs/{$locale}");
    }

    private function converter(): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'apply_id_to_heading' => true,
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'after',
                'symbol' => '#',
                'aria_hidden' => true,
                'min_heading_level' => 2,
                'max_heading_level' => 3,
                'html_class' => 'heading-permalink',
                'title' => '',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        return new MarkdownConverter($environment);
    }

    /**
     * All articles of a locale in reading order.
     *
     * @return array<int, array{slug: string, title: string, description: ?string, order: int, icon: string, category: string, minutes: int, excerpt: string, text: string}>
     */
    public function all(string $locale): array
    {
        if (isset($this->cache[$locale])) {
            return $this->cache[$locale];
        }

        $directory = $this->directory($locale);

        if (! File::isDirectory($directory)) {
            return $this->cache[$locale] = [];
        }

        $articles = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $slug = $file->getFilenameWithoutExtension();

            if (! preg_match('/\A[a-zA-Z0-9_-]+\z/', $slug)) {
                continue;
            }

            [$meta, $body] = $this->parse(File::get($file->getPathname()));
            $text = $this->plainText($body);

            $articles[] = [
                'slug' => $slug,
                'title' => $this->title($body, $slug),
                'description' => $meta['description'] ?? null,
                'order' => (int) ($meta['order'] ?? 1000),
                'icon' => $meta['icon'] ?? 'book',
                'category' => in_array($meta['category'] ?? null, self::CATEGORIES, true) ? $meta['category'] : 'start',
                'minutes' => $this->minutes($body),
                'excerpt' => Str::limit($meta['description'] ?? $text, 180),
                'text' => $text,
            ];
        }

        // Reading order: by category, then by order within it.
        $rank = array_flip(self::CATEGORIES);
        usort($articles, fn (array $a, array $b): int => [$rank[$a['category']], $a['order'], $a['title']] <=> [$rank[$b['category']], $b['order'], $b['title']]);

        return $this->cache[$locale] = $articles;
    }

    /**
     * Articles grouped by category, in display order (empty groups left out).
     *
     * @return array<string, array<int, array>>
     */
    public function grouped(string $locale): array
    {
        $groups = array_fill_keys(self::CATEGORIES, []);

        foreach ($this->all($locale) as $article) {
            $groups[$article['category']][] = $article;
        }

        return array_filter($groups);
    }

    public function exists(string $locale, string $slug): bool
    {
        return in_array($slug, array_column($this->all($locale), 'slug'), true);
    }

    public function find(string $locale, string $slug): ?array
    {
        if (! preg_match('/\A[a-zA-Z0-9_-]+\z/', $slug)) {
            return null;
        }

        $path = $this->directory($locale).DIRECTORY_SEPARATOR.$slug.'.md';

        if (! File::exists($path)) {
            return null;
        }

        [$meta, $body] = $this->parse(File::get($path));
        // The page template prints the title; render the body without it.
        $withoutTitle = preg_replace('/\A\s*# .*\R/', '', $body, 1) ?? $body;
        $html = $this->callouts($this->converter()->convert($withoutTitle)->getContent(), $locale);

        $articles = $this->all($locale);
        $index = array_search($slug, array_column($articles, 'slug'), true);
        $neighbour = fn (int $offset): ?array => $index !== false && isset($articles[$index + $offset])
            ? ['slug' => $articles[$index + $offset]['slug'], 'title' => $articles[$index + $offset]['title']]
            : null;

        $current = $index !== false ? $articles[$index] : null;

        return [
            'slug' => $slug,
            'title' => $this->title($body, $slug),
            'description' => $meta['description'] ?? null,
            'category' => $current['category'] ?? 'start',
            'minutes' => $current['minutes'] ?? $this->minutes($body),
            'content' => $body,
            'html' => $html,
            'toc' => $this->toc($html),
            'previous' => $neighbour(-1),
            'next' => $neighbour(1),
        ];
    }

    /**
     * Simple full-text search over title, description and body.
     *
     * @return array<int, array{slug: string, title: string, description: ?string, category: string, snippet: string}>
     */
    public function search(string $locale, string $query): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/u', mb_strtolower(trim($query))) ?: [], fn ($t) => mb_strlen($t) >= 2));

        if ($terms === []) {
            return [];
        }

        $results = [];

        foreach ($this->all($locale) as $article) {
            $title = mb_strtolower($article['title']);
            $description = mb_strtolower((string) $article['description']);
            $text = mb_strtolower($article['text']);
            $score = 0;

            foreach ($terms as $term) {
                $hit = (str_contains($title, $term) ? 5 : 0) + (str_contains($description, $term) ? 3 : 0) + min(substr_count($text, $term), 5);

                if ($hit === 0) {
                    continue 2; // every term must match somewhere
                }

                $score += $hit;
            }

            $results[] = [
                'slug' => $article['slug'],
                'title' => $article['title'],
                'description' => $article['description'],
                'category' => $article['category'],
                'snippet' => $this->snippet($article['text'], $terms[0]),
                'score' => $score,
            ];
        }

        usort($results, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(fn (array $r) => array_diff_key($r, ['score' => true]), $results);
    }

    /**
     * @return array{0: array<string, string>, 1: string}
     */
    private function parse(string $content): array
    {
        $content = str_replace("\r\n", "\n", $content);
        $meta = [];

        if (preg_match('/\A---\n(.*?)\n---\n/s', $content, $m) === 1) {
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/\A([a-z_]+):\s*(.*)\z/', trim($line), $kv) === 1) {
                    $meta[$kv[1]] = trim($kv[2], " \"'");
                }
            }

            $content = substr($content, strlen($m[0]));
        }

        return [$meta, str_replace('{app_url}', (string) config('serverscope.app_url'), $content)];
    }

    private function callouts(string $html, string $locale): string
    {
        $labels = self::CALLOUTS[$locale] ?? self::CALLOUTS['en'];

        return preg_replace_callback(
            '~<blockquote>\s*<p>\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*~',
            fn (array $m) => '<blockquote role="note" class="callout callout-'.strtolower($m[1]).'"><p class="callout-title">'.e($labels[$m[1]]).'</p><p>',
            $html,
        ) ?? $html;
    }

    /**
     * @return array<int, array{id: string, title: string}>
     */
    private function toc(string $html): array
    {
        preg_match_all('~<h2 id="([^"]+)">(.*?)</h2>~s', $html, $matches, PREG_SET_ORDER);

        return array_map(fn (array $m) => [
            'id' => $m[1],
            'title' => trim(html_entity_decode(strip_tags(preg_replace('~<a [^>]*class="heading-permalink".*?</a>~s', '', $m[2]) ?? ''), ENT_QUOTES)),
        ], $matches);
    }

    private function plainText(string $markdown): string
    {
        $text = preg_replace('/```.*?```/s', ' ', $markdown) ?? $markdown;
        $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text) ?? $text;
        $text = preg_replace('/^#+\s.*$/m', ' ', $text, 1) ?? $text;
        $text = preg_replace('/\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]|[#>*_`|]/', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /** Reading time in minutes at ~200 words per minute (code blocks count half). */
    private function minutes(string $markdown): int
    {
        preg_match_all('/```.*?```/s', $markdown, $code);
        $codeWords = str_word_count(implode(' ', $code[0]));
        $words = str_word_count(preg_replace('/```.*?```/s', ' ', $markdown) ?? $markdown);

        return max(1, (int) round(($words + $codeWords / 2) / 200));
    }

    private function snippet(string $text, string $term): string
    {
        $position = mb_stripos($text, $term);

        if ($position === false) {
            return Str::limit($text, 180);
        }

        $start = max(0, $position - 60);

        return ($start > 0 ? '…' : '').trim(mb_substr($text, $start, 180)).(mb_strlen($text) > $start + 180 ? '…' : '');
    }

    private function title(string $content, string $fallback): string
    {
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            if (str_starts_with(trim($line), '# ')) {
                return trim(substr(trim($line), 2));
            }
        }

        return Str::headline($fallback);
    }
}
