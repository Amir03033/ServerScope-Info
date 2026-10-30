<?php

namespace App\Services\Site;

use App\Enums\PublishStatus;
use App\Models\Feature;
use App\Models\Release;
use Illuminate\Support\Facades\File;

/**
 * Imports the existing changelog into the releases table:
 *
 * - version 0.1.0 from lang/{nl,en}/changelog.php (item list);
 * - content/changelog/{nl,en}/*.md (front matter: date, title, version,
 *   status, feature), stored byte for byte as Markdown body.
 *
 * Keyed on source_key per locale, so re-running creates no duplicates.
 * Entries changed in the admin panel since their import are left alone.
 */
class ChangelogImporter
{
    /**
     * @return array<int, array{locale: string, key: string, action: string}>
     */
    public function run(): array
    {
        $report = [];

        foreach (['nl', 'en'] as $locale) {
            foreach ((array) trans('changelog.entries', [], $locale) as $entry) {
                $items = array_map(fn (array $item) => ['type' => $item[0], 'text' => $item[1]], $entry['items']);
                $report[] = $this->upsert($locale, 'lang:'.$entry['version'], [
                    'version' => $entry['version'],
                    'title' => null,
                    'released_at' => $entry['date'],
                    'items' => $items,
                    'body' => null,
                    'status' => PublishStatus::Published,
                ], hash('sha256', json_encode($items)));
            }

            foreach (File::glob(base_path("content/changelog/{$locale}/*.md")) as $path) {
                $raw = File::get($path);
                preg_match('/\A---\r?\n(.*?)\r?\n---\r?\n/s', $raw, $m);
                $meta = [];

                foreach (preg_split('/\r?\n/', $m[1] ?? '') ?: [] as $line) {
                    if (preg_match('/\A([a-z_]+):\s*(.*)\z/', trim($line), $kv) === 1) {
                        $meta[$kv[1]] = trim($kv[2], " \"'");
                    }
                }

                $body = substr($raw, strlen($m[0] ?? ''));

                $report[] = $this->upsert($locale, 'file:'.basename($path, '.md'), [
                    'version' => $meta['version'] ?? '',
                    'title' => $meta['title'] ?? null,
                    'released_at' => $meta['date'],
                    'items' => [],
                    'body' => $body,
                    'status' => PublishStatus::tryFrom($meta['status'] ?? '') ?? PublishStatus::Draft,
                    'feature_id' => isset($meta['feature']) ? Feature::query()->where('key', $meta['feature'])->value('id') : null,
                ], hash('sha256', $body));
            }
        }

        return $report;
    }

    private function upsert(string $locale, string $key, array $attributes, string $sha): array
    {
        $release = Release::query()->where('locale', $locale)->where('source_key', $key)->first();

        // Before this import existed, 0.1.0 may have been entered by hand.
        if ($release === null && $attributes['version'] !== '') {
            $release = Release::query()->where('locale', $locale)->whereNull('source_key')->where('version', $attributes['version'])->first();

            if ($release !== null) {
                $release->forceFill(['source_key' => $key])->save();

                return ['locale' => $locale, 'key' => $key, 'action' => 'bestond al (handmatig), niet gewijzigd'];
            }
        }

        if ($release === null) {
            $release = new Release(['locale' => $locale, ...$attributes]);
            $release->forceFill(['source_key' => $key, 'source_sha256' => $sha])->save();

            return ['locale' => $locale, 'key' => $key, 'action' => 'aangemaakt'];
        }

        $current = hash('sha256', $release->body ?? json_encode($release->items));

        if ($current !== $release->source_sha256) {
            return ['locale' => $locale, 'key' => $key, 'action' => 'overgeslagen: bewerkt in Filament'];
        }

        if ($release->source_sha256 === $sha) {
            return ['locale' => $locale, 'key' => $key, 'action' => 'ongewijzigd'];
        }

        // The source changed and the entry was not edited: update the
        // content, keep the owner's status and date.
        $release->fill(collect($attributes)->only(['title', 'items', 'body'])->all());
        $release->forceFill(['source_sha256' => $sha])->save();

        return ['locale' => $locale, 'key' => $key, 'action' => 'bijgewerkt'];
    }
}
