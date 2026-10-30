<?php

namespace App\Services\Legal;

use App\Models\LegalDocument;
use App\Models\LegalVersion;
use Illuminate\Support\Facades\File;

/**
 * Creates the legal documents and their first DRAFT version from
 * content/legal/{locale}/{key}.md. Repeatable: existing documents and
 * versions are never changed.
 */
class LegalImporter
{
    /**
     * @return array<int, array{locale: string, key: string, action: string}>
     */
    public function run(): array
    {
        $report = [];

        foreach (['nl', 'en'] as $locale) {
            foreach (LegalDocument::DOCUMENTS as $key => [$nlSlug, $enSlug, $sort]) {
                $path = base_path("content/legal/{$locale}/{$key}.md");

                if (! File::exists($path)) {
                    continue;
                }

                [$meta, $body] = $this->parse(File::get($path));

                $document = LegalDocument::query()->firstOrCreate(
                    ['key' => $key, 'locale' => $locale],
                    ['slug' => LegalDocument::slugFor($key, $locale), 'title' => $meta['title'] ?? $key, 'seo_description' => $meta['description'] ?? null, 'sort' => $sort],
                );

                if ($document->versions()->exists()) {
                    $report[] = ['locale' => $locale, 'key' => $key, 'action' => 'ongewijzigd'];

                    continue;
                }

                $version = new LegalVersion([
                    'legal_document_id' => $document->id,
                    'version' => $meta['version'] ?? '0.1',
                    'body' => $body,
                    'changelog' => $meta['changes'] ?? null,
                    'status' => 'draft',
                ]);
                $version->revisionReason = 'Concept geïmporteerd uit content/legal/'.$locale.'/'.$key.'.md';
                $version->save();

                $report[] = ['locale' => $locale, 'key' => $key, 'action' => 'concept aangemaakt'];
            }
        }

        return $report;
    }

    /**
     * @return array{0: array<string, string>, 1: string}
     */
    private function parse(string $raw): array
    {
        $meta = [];

        if (preg_match('/\A---\r?\n(.*?)\r?\n---\r?\n/s', $raw, $m) === 1) {
            foreach (preg_split('/\r?\n/', $m[1]) ?: [] as $line) {
                if (preg_match('/\A([a-z_]+):\s*(.*)\z/', trim($line), $kv) === 1) {
                    $meta[$kv[1]] = trim($kv[2], " \"'");
                }
            }

            $raw = substr($raw, strlen($m[0]));
        }

        return [$meta, $raw];
    }
}
