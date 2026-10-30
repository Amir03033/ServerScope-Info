<?php

namespace App\Services;

use App\Models\Doc;
use App\Services\Docs\MarkdownFileSource;
use App\Support\Markdown\MarkdownRenderer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Documentation for the public site.
 *
 * Source: the `docs` table (managed in the admin panel). Until the import
 * has run (no rows at all), the original Markdown files in
 * content/docs/{locale} are used, so the site never shows an empty
 * documentation section.
 *
 * Only visible pages are returned: published, due (scheduled publishing)
 * and not tied to a hidden feature. Pages of a "coming soon" feature are
 * returned with coming_soon = true.
 */
class DocumentationService
{
    /** @var array<string, array<int, array>> */
    private array $cache = [];

    private ?bool $database = null;

    public function __construct(
        private readonly MarkdownRenderer $renderer,
        private readonly MarkdownFileSource $files,
    ) {}

    public function usesDatabase(): bool
    {
        return $this->database ??= (bool) rescue(fn () => Doc::withTrashed()->exists(), false, report: false);
    }

    /**
     * All visible articles of a locale in reading order (category, then page).
     *
     * @return array<int, array{id: ?int, slug: string, title: string, description: ?string, order: int, icon: string, excerpt: string, text: string, category: ?array{key: string, name: string, sort: int}, coming_soon: bool, updated_at: ?Carbon}>
     */
    public function all(string $locale): array
    {
        abort_unless(in_array($locale, MarkdownFileSource::LOCALES, true), 404);

        return $this->cache[$locale] ??= $this->usesDatabase() ? $this->fromDatabase($locale) : $this->fromFiles($locale);
    }

    public function find(string $locale, string $slug): ?array
    {
        if (! preg_match('/\A[a-zA-Z0-9_-]+\z/', $slug)) {
            return null;
        }

        $articles = $this->all($locale);
        $index = array_search($slug, array_column($articles, 'slug'), true);

        if ($index === false) {
            return null;
        }

        if ($this->usesDatabase()) {
            $doc = Doc::query()->with(['feature', 'category'])->find($articles[$index]['id']);
            $body = $doc->body;
            $seo = ['seo_title' => $doc->seo_title, 'seo_description' => $doc->seo_description, 'noindex' => $doc->noindex, 'summary' => $doc->summary];
        } else {
            $page = collect($this->files->all($locale))->firstWhere('slug', $slug);
            $body = $page['body'];
            $seo = ['seo_title' => null, 'seo_description' => null, 'noindex' => false, 'summary' => null];
        }

        $body = MarkdownRenderer::withSummary($body, $seo['summary']);
        $rendered = $this->renderer->render($body, $locale);
        $neighbour = fn (int $offset): ?array => isset($articles[$index + $offset])
            ? ['slug' => $articles[$index + $offset]['slug'], 'title' => $articles[$index + $offset]['title']]
            : null;

        return [
            ...$articles[$index],
            ...$seo,
            'content' => $body,
            'html' => $rendered['html'],
            'toc' => $rendered['toc'],
            'previous' => $neighbour(-1),
            'next' => $neighbour(1),
        ];
    }

    /**
     * Rendering of any page (also drafts) for the admin preview.
     *
     * @return array{html: string, toc: array<int, array{id: string, title: string}>}
     */
    public function render(string $body, string $locale): array
    {
        return $this->renderer->render($body, $locale);
    }

    /**
     * Simple full-text search over title, description and body.
     *
     * @return array<int, array{slug: string, title: string, description: ?string, snippet: string}>
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
                'snippet' => $this->snippet($article['text'], $terms[0]),
                'score' => $score,
            ];
        }

        usort($results, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(fn (array $r) => array_diff_key($r, ['score' => true]), $results);
    }

    /**
     * Compact index for the ⌘K search dialog.
     *
     * @return array<int, array{t: string, d: string, u: string, c: string, x: string}>
     */
    public function searchIndex(string $locale): array
    {
        return array_map(fn (array $a): array => [
            't' => $a['title'],
            'd' => (string) $a['description'],
            'u' => url("{$locale}/docs/{$a['slug']}"),
            'c' => (string) ($a['category']['name'] ?? ''),
            'x' => Str::limit($a['text'], 6000, ''),
        ], $this->all($locale));
    }

    public function flush(): void
    {
        $this->cache = [];
        $this->database = null;
    }

    private function fromDatabase(string $locale): array
    {
        return Doc::query()
            ->visible()
            ->where('locale', $locale)
            ->with(['category', 'feature'])
            ->get()
            ->sortBy(fn (Doc $doc) => [$doc->category?->sort ?? PHP_INT_MAX, $doc->sort, $doc->title])
            ->values()
            ->map(function (Doc $doc): array {
                $text = $this->renderer->plainText($doc->body);

                return [
                    'id' => $doc->id,
                    'slug' => $doc->slug,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'order' => $doc->sort,
                    'icon' => $doc->icon,
                    'excerpt' => Str::limit($doc->description ?: $text, 180),
                    'text' => $text,
                    'category' => $doc->category ? ['key' => $doc->category->translation_key, 'name' => $doc->category->name, 'sort' => $doc->category->sort] : null,
                    'coming_soon' => $doc->isComingSoon(),
                    'updated_at' => $doc->updated_at,
                ];
            })
            ->all();
    }

    private function fromFiles(string $locale): array
    {
        return array_map(function (array $page): array {
            $text = $this->renderer->plainText($page['body']);

            return [
                'id' => null,
                'slug' => $page['slug'],
                'title' => $page['title'],
                'description' => $page['meta']['description'] ?? null,
                'order' => (int) ($page['meta']['order'] ?? 1000),
                'icon' => $page['meta']['icon'] ?? 'book',
                'excerpt' => Str::limit($page['meta']['description'] ?? $text, 180),
                'text' => $text,
                'category' => null,
                'coming_soon' => false,
                'updated_at' => null,
            ];
        }, $this->files->all($locale));
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
}
