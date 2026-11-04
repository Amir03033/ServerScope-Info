<?php

namespace App\Services\Docs;

use App\Models\Doc;
use App\Models\DocRevision;
use Illuminate\Support\Facades\File;

/**
 * Imports content/docs-drafts/{locale}/{slug}.md as DRAFT revisions of an
 * existing page. The published page is not touched; a draft only goes
 * live when someone publishes it in the admin panel. Used for pages that
 * were edited in the panel, which the regular import no longer updates.
 * Repeatable: the same draft is never added twice, and a draft that is
 * already the published text is skipped.
 */
class DraftRevisionImporter
{
    public const REASON = 'Concept: bijgewerkt voor de functies van november 2026 (plannen, chatkanalen, geheimen)';

    /**
     * @return array<int, array{locale: string, slug: string, action: string}>
     */
    public function run(): array
    {
        $report = [];

        foreach (MarkdownFileSource::LOCALES as $locale) {
            foreach (File::glob(base_path("content/docs-drafts/{$locale}/*.md")) as $path) {
                $slug = basename($path, '.md');
                $body = File::get($path);
                $doc = Doc::query()->where('locale', $locale)->where('slug', $slug)->first();

                if ($doc === null) {
                    $report[] = ['locale' => $locale, 'slug' => $slug, 'action' => 'overgeslagen: pagina bestaat niet'];

                    continue;
                }

                if (hash('sha256', $doc->body) === hash('sha256', $body)) {
                    $report[] = ['locale' => $locale, 'slug' => $slug, 'action' => 'ongewijzigd: al gepubliceerd'];

                    continue;
                }

                $exists = DocRevision::query()->where('doc_id', $doc->id)->where('is_draft', true)->get()
                    ->contains(fn (DocRevision $r) => hash('sha256', $r->body) === hash('sha256', $body));

                if ($exists) {
                    $report[] = ['locale' => $locale, 'slug' => $slug, 'action' => 'ongewijzigd'];

                    continue;
                }

                DocRevision::query()->create([
                    'doc_id' => $doc->id,
                    'reason' => self::REASON,
                    'is_draft' => true,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'body' => $body,
                    'meta' => ['source' => 'content/docs-drafts/'.$locale.'/'.$slug.'.md'],
                ]);

                $report[] = ['locale' => $locale, 'slug' => $slug, 'action' => 'concept aangemaakt'];
            }
        }

        return $report;
    }
}
