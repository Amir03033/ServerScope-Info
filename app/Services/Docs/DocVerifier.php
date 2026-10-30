<?php

namespace App\Services\Docs;

use App\Models\Doc;
use App\Support\Markdown\MarkdownRenderer;

/**
 * Proves that every original Markdown page is in the database with exactly
 * the same content: counts per locale, SHA-256 of the body, the front matter
 * fields, the title, the SHA-256 of the rendered HTML, and that the page is
 * visible on the site.
 */
class DocVerifier
{
    public function __construct(
        private readonly MarkdownFileSource $files,
        private readonly MarkdownRenderer $renderer,
    ) {}

    /**
     * @return array{ok: bool, generated_at: string, counts: array<string, array{files: int, records: int}>, pages: array<int, array<string, mixed>>}
     */
    public function run(): array
    {
        $pages = [];
        $counts = [];

        foreach (MarkdownFileSource::LOCALES as $locale) {
            $files = $this->files->all($locale);
            $counts[$locale] = [
                'files' => count($files),
                'records' => Doc::query()->where('locale', $locale)->whereIn('slug', array_column($files, 'slug'))->count(),
            ];

            foreach ($files as $page) {
                $doc = Doc::query()->with('feature')->where('locale', $locale)->where('slug', $page['slug'])->first();
                $dbBody = $doc?->body;

                $checks = [
                    'record' => $doc !== null,
                    'body_sha256' => $dbBody !== null && hash('sha256', $dbBody) === $page['body_sha256'],
                    'title' => $doc?->title === $page['title'],
                    'description' => $doc?->description === ($page['meta']['description'] ?? null),
                    'order' => $doc?->sort === (int) ($page['meta']['order'] ?? 1000),
                    'icon' => $doc?->icon === ($page['meta']['icon'] ?? 'book'),
                    'html_sha256' => $dbBody !== null && $this->htmlHash($dbBody, $locale) === $this->htmlHash($page['body'], $locale),
                    'visible' => $doc?->isVisible() ?? false,
                ];

                $pages[] = [
                    'locale' => $locale,
                    'slug' => $page['slug'],
                    'file' => $page['relative_path'],
                    'file_body_sha256' => $page['body_sha256'],
                    'db_body_sha256' => $dbBody !== null ? hash('sha256', $dbBody) : null,
                    'checks' => $checks,
                    'ok' => ! in_array(false, $checks, true),
                ];
            }
        }

        $ok = collect($pages)->every('ok') && collect($counts)->every(fn ($c) => $c['files'] === $c['records']);

        return ['ok' => $ok, 'generated_at' => now()->toIso8601String(), 'counts' => $counts, 'pages' => $pages];
    }

    private function htmlHash(string $body, string $locale): string
    {
        return hash('sha256', $this->renderer->render($body, $locale)['html']);
    }
}
