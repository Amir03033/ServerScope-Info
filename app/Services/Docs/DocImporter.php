<?php

namespace App\Services\Docs;

use App\Enums\PublishStatus;
use App\Models\Doc;
use App\Models\Feature;
use Illuminate\Support\Facades\DB;

/**
 * Imports content/docs/{locale}/{slug}.md into the docs table.
 *
 * - Idempotent: keyed on (locale, slug); a second run changes nothing.
 * - The body is stored byte for byte (everything after the front matter).
 * - Pages edited in the admin panel after the import, or deleted there,
 *   are never overwritten or restored (unless $force).
 */
class DocImporter
{
    public const CREATED = 'aangemaakt';

    public const UPDATED = 'bijgewerkt';

    public const UNCHANGED = 'ongewijzigd';

    public const SKIPPED_EDITED = 'overgeslagen: na import bewerkt in Filament';

    public const SKIPPED_DELETED = 'overgeslagen: in Filament verwijderd';

    public function __construct(
        private readonly MarkdownFileSource $files,
        private readonly DocCategories $categories,
    ) {}

    /**
     * @return array<int, array{locale: string, slug: string, action: string}>
     */
    public function run(bool $force = false, ?MarkdownFileSource $source = null): array
    {
        $report = [];
        $source ??= $this->files;

        DB::transaction(function () use (&$report, $force, $source): void {
            $this->categories->ensure();

            foreach (MarkdownFileSource::LOCALES as $locale) {
                foreach ($source->all($locale) as $page) {
                    $report[] = ['locale' => $locale, 'slug' => $page['slug'], 'action' => $this->import($page, $force)];
                }
            }
        });

        return $report;
    }

    private function import(array $page, bool $force): string
    {
        $doc = Doc::withTrashed()->where('locale', $page['locale'])->where('slug', $page['slug'])->first();

        if ($doc?->trashed() && ! $force) {
            return self::SKIPPED_DELETED;
        }

        $attributes = [
            'translation_key' => $page['slug'],
            'title' => $page['title'],
            'description' => $page['meta']['description'] ?? null,
            'body' => $page['body'],
            'icon' => $page['meta']['icon'] ?? 'book',
            'sort' => (int) ($page['meta']['order'] ?? 1000),
        ];

        if ($doc === null) {
            $doc = new Doc([
                ...$attributes,
                'locale' => $page['locale'],
                'slug' => $page['slug'],
                'status' => PublishStatus::Published, // the original pages are live today; new ones follow their feature
                'doc_category_id' => $this->categories->id($page['locale'], $page['meta']['category'] ?? DocCategories::ORIGINAL[$page['slug']] ?? 'start'),
                'feature_id' => isset($page['meta']['feature']) ? Feature::query()->where('key', $page['meta']['feature'])->value('id') : null,
            ]);
            $this->stamp($doc, $page);
            $doc->save();

            return self::CREATED;
        }

        $matches = collect($attributes)->every(fn ($value, $key) => $doc->getAttribute($key) === $value);

        if ($matches && ! $doc->trashed() && $doc->source_sha256 === $page['file_sha256']) {
            return self::UNCHANGED;
        }

        if (! $force && $doc->editedSinceImport()) {
            return $matches ? self::UNCHANGED : self::SKIPPED_EDITED;
        }

        if ($doc->trashed()) {
            $doc->restore();
        }

        $doc->fill($attributes);
        $this->stamp($doc, $page);
        $doc->save();

        return self::UPDATED;
    }

    private function stamp(Doc $doc, array $page): void
    {
        $doc->source_path = $page['relative_path'];
        $doc->source_sha256 = $page['file_sha256'];
        $doc->imported_body_sha256 = $page['body_sha256'];
        $doc->imported_at = now();
        $doc->revisionReason = 'Import uit '.$page['relative_path'];
    }
}
