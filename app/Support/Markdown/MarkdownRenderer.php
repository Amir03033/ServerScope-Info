<?php

namespace App\Support\Markdown;

use App\Models\Setting;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Tempest\Highlight\Highlighter;

/**
 * Renders documentation Markdown to safe HTML, the same way for the site,
 * the admin preview and the import verification:
 *
 * - raw HTML is stripped and unsafe links are dropped;
 * - {app_url} becomes the ServerScope application URL and
 *   {release_fingerprint} the fingerprint from the site settings;
 * - h2/h3 get ids and a permalink;
 * - GitHub alerts (> [!NOTE] ...) become callouts;
 * - fenced code is highlighted on the server (tempest/highlight) and gets
 *   a data-lang attribute; there is no client-side highlighter.
 */
class MarkdownRenderer
{
    public const CALLOUTS = [
        'nl' => ['NOTE' => 'Opmerking', 'TIP' => 'Tip', 'IMPORTANT' => 'Belangrijk', 'WARNING' => 'Let op', 'CAUTION' => 'Pas op', 'SUMMARY' => 'In het kort'],
        'en' => ['NOTE' => 'Note', 'TIP' => 'Tip', 'IMPORTANT' => 'Important', 'WARNING' => 'Warning', 'CAUTION' => 'Caution', 'SUMMARY' => 'In short'],
    ];

    /** Added to every "In short" box (legal texts): the full text applies. */
    public const SUMMARY_NOTE = [
        'nl' => 'Dit is een samenvatting. De volledige tekst hieronder geldt.',
        'en' => 'This is a summary. The full text below applies.',
    ];

    /** Shown until the owner fills in the release key fingerprint. */
    public const FINGERPRINT_PLACEHOLDER = [
        'nl' => '(volgt zodra de releasesleutel is aangemaakt)',
        'en' => '(to follow once the release key has been created)',
    ];

    private ?MarkdownConverter $converter = null;

    /**
     * @return array{html: string, toc: array<int, array{id: string, title: string}>}
     */
    public function render(string $markdown, string $locale, bool $withoutTitle = true): array
    {
        $markdown = $this->prepare($markdown, $locale);

        if ($withoutTitle) {
            // The page template prints the title; render the body without it.
            $markdown = preg_replace('/\A\s*# .*\R/', '', $markdown, 1) ?? $markdown;
        }

        $html = $this->callouts($this->converter()->convert($markdown)->getContent(), $locale);

        return ['html' => $html, 'toc' => $this->toc($html)];
    }

    public function prepare(string $markdown, string $locale = 'nl'): string
    {
        $fingerprint = rescue(fn () => Setting::get('release_fingerprint'), null, report: false);

        return str_replace(
            ['{app_url}', '{release_fingerprint}', "\r\n"],
            [(string) config('serverscope.app_url'), filled($fingerprint) ? (string) $fingerprint : (self::FINGERPRINT_PLACEHOLDER[$locale] ?? self::FINGERPRINT_PLACEHOLDER['en']), "\n"],
            $markdown,
        );
    }

    /**
     * Title from the first "# " line, if any.
     */
    public function title(string $markdown): ?string
    {
        foreach (preg_split('/\r\n|\r|\n/', $markdown) ?: [] as $line) {
            if (str_starts_with(trim($line), '# ')) {
                return trim(substr(trim($line), 2));
            }
        }

        return null;
    }

    public function plainText(string $markdown): string
    {
        $text = $this->prepare($markdown);
        $text = preg_replace('/```.*?```/s', ' ', $text) ?? $text;
        $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text) ?? $text;
        $text = preg_replace('/^#+\s.*$/m', ' ', $text, 1) ?? $text;
        $text = preg_replace('/\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION|SUMMARY)\]|[#>*_`|]/', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    private function converter(): MarkdownConverter
    {
        if ($this->converter !== null) {
            return $this->converter;
        }

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
        $environment->addRenderer(FencedCode::class, $this->codeRenderer(), 10);

        return $this->converter = new MarkdownConverter($environment);
    }

    private function codeRenderer(): NodeRendererInterface
    {
        $highlighter = (new Highlighter)->addLanguage(new ShellLanguage);
        $supported = array_merge($highlighter->getSupportedLanguageNames(), ['sh', 'bash', 'shell', 'console', 'zsh']);

        return new class($highlighter, $supported) implements NodeRendererInterface
        {
            /** @param array<int, string> $supported */
            public function __construct(private Highlighter $highlighter, private array $supported) {}

            public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
            {
                /** @var FencedCode $node */
                $language = strtolower((string) preg_replace('/[^\w-].*$/', '', $node->getInfoWords()[0] ?? ''));
                $code = $node->getLiteral();
                $label = $language !== '' ? ' data-lang="'.e($language).'"' : '';

                $inner = in_array($language, $this->supported, true)
                    ? $this->highlighter->parse(rtrim($code, "\n"), $language)
                    : e(rtrim($code, "\n"));

                $class = $language !== '' ? ' class="language-'.e($language).'"' : '';

                return "<pre{$label}><code{$class}>{$inner}\n</code></pre>\n";
            }
        };
    }

    private function callouts(string $html, string $locale): string
    {
        $labels = self::CALLOUTS[$locale] ?? self::CALLOUTS['en'];

        $html = preg_replace_callback(
            '~<blockquote>\s*<p>\[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION|SUMMARY)\]\s*~',
            fn (array $m) => '<blockquote class="callout callout-'.strtolower($m[1]).'"><p class="callout-title">'.e($labels[$m[1]]).'</p><p>',
            $html,
        ) ?? $html;

        $note = self::SUMMARY_NOTE[$locale] ?? self::SUMMARY_NOTE['en'];

        return preg_replace(
            '~(<blockquote class="callout callout-summary">.*?)(</blockquote>)~s',
            '$1<p class="callout-note">'.e($note).'</p>$2',
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
}
