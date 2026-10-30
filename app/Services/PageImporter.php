<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\Feature;
use App\Models\Page;
use Illuminate\Support\Facades\File;

/**
 * Creates the content pages from content/pages/{locale}/{key}.md.
 * Repeatable: a page that exists is never changed (edits in the admin panel
 * win). A "## Veelgestelde vragen" / "## Frequently asked questions" section
 * with "### question" headings becomes the visible FAQ of the page.
 */
class PageImporter
{
    public const FAQ_HEADINGS = ['Veelgestelde vragen', 'Frequently asked questions'];

    /**
     * @return array<int, array{locale: string, key: string, action: string}>
     */
    public function run(): array
    {
        $report = [];
        $sort = 0;

        foreach (Page::PAGES as $key => [, , $featureKey]) {
            $sort += 10;

            foreach (['nl', 'en'] as $locale) {
                $path = base_path("content/pages/{$locale}/{$key}.md");

                if (! File::exists($path)) {
                    continue;
                }

                if (Page::query()->where('key', $key)->where('locale', $locale)->exists()) {
                    $report[] = ['locale' => $locale, 'key' => $key, 'action' => 'ongewijzigd'];

                    continue;
                }

                [$meta, $body] = $this->parse(File::get($path));
                [$body, $faq] = $this->extractFaq($body);

                $page = new Page([
                    'key' => $key,
                    'locale' => $locale,
                    'slug' => Page::slugFor($key, $locale),
                    'title' => $meta['title'] ?? $key,
                    'summary' => $meta['summary'] ?? null,
                    'body' => trim($body)."\n",
                    'faq' => $faq ?: null,
                    'seo_title' => $meta['seo_title'] ?? null,
                    'seo_description' => $meta['description'] ?? null,
                    'status' => PublishStatus::Published,
                    'feature_id' => $featureKey ? Feature::query()->where('key', $featureKey)->value('id') : null,
                    'sort' => $sort,
                ]);
                $page->revisionReason = "Geïmporteerd uit content/pages/{$locale}/{$key}.md";
                $page->save();

                $report[] = ['locale' => $locale, 'key' => $key, 'action' => 'aangemaakt'];
            }
        }

        return $report;
    }

    /**
     * @return array{0: string, 1: list<array{question: string, answer: string}>}
     */
    public function extractFaq(string $body): array
    {
        $pattern = '/^## (?:'.implode('|', array_map('preg_quote', self::FAQ_HEADINGS)).')\s*\n(.*?)(?=^## |\z)/ms';

        if (preg_match($pattern, $body, $m) !== 1) {
            return [$body, []];
        }

        $faq = [];
        foreach (preg_split('/^### /m', $m[1]) as $chunk) {
            if (trim($chunk) === '') {
                continue;
            }
            [$question, $answer] = array_pad(explode("\n", $chunk, 2), 2, '');
            $faq[] = ['question' => trim($question), 'answer' => trim($answer)];
        }

        return [str_replace($m[0], '', $body), $faq];
    }

    /**
     * @return array{0: array<string, string>, 1: string}
     */
    private function parse(string $raw): array
    {
        $meta = [];
        $raw = str_replace("\r\n", "\n", $raw);

        if (preg_match('/\A---\n(.*?)\n---\n/s', $raw, $m) === 1) {
            foreach (explode("\n", $m[1]) as $line) {
                if (preg_match('/\A([a-z_]+):\s*(.*)\z/', trim($line), $kv) === 1) {
                    $meta[$kv[1]] = trim($kv[2], " \"'");
                }
            }

            $raw = substr($raw, strlen($m[0]));
        }

        return [$meta, $raw];
    }
}
