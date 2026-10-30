<?php

namespace App\Services\Legal;

use App\Models\LegalVersion;
use App\Support\Markdown\MarkdownRenderer;

/**
 * Renders a legal text: company placeholders filled in, numbered chapters
 * with ids, "In short" boxes, and HTML comments (notes for the lawyer)
 * removed, since raw HTML is never rendered.
 */
class LegalRenderer
{
    public function __construct(
        private readonly MarkdownRenderer $markdown,
        private readonly CompanyData $company,
    ) {}

    /**
     * @return array{html: string, toc: array<int, array{id: string, title: string}>, changelog: ?string}
     */
    public function render(LegalVersion $version): array
    {
        $locale = $version->document->locale;
        $rendered = $this->markdown->render($this->fill($version->body, $locale), $locale, withoutTitle: false);

        return [
            'html' => $rendered['html'],
            'toc' => $rendered['toc'],
            'changelog' => filled($version->changelog) ? $this->markdown->render($this->fill($version->changelog, $locale), $locale, withoutTitle: false)['html'] : null,
        ];
    }

    /**
     * Placeholders filled in and lawyer notes (<!-- … -->) removed.
     */
    public function fill(string $markdown, string $locale): string
    {
        $markdown = preg_replace('~<!--.*?-->\s*~s', '', $markdown) ?? $markdown;

        return $this->company->fill($markdown, $locale);
    }

    /**
     * Plain Markdown for the .md version and llms-full.txt.
     */
    public function markdown(LegalVersion $version): string
    {
        return $this->markdown->prepare($this->fill($version->body, $version->document->locale), $version->document->locale);
    }
}
