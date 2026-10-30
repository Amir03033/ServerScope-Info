<?php

namespace Tests\Feature;

use App\Models\Doc;
use App\Models\DocRevision;
use App\Models\Feature;
use App\Services\Docs\DocImporter;
use App\Services\Docs\DocVerifier;
use App\Services\Docs\MarkdownFileSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DocImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function fileCount(): int
    {
        return count(File::glob(base_path('content/docs/nl/*.md'))) + count(File::glob(base_path('content/docs/en/*.md')));
    }

    public function test_import_stores_every_file_byte_for_byte_and_is_repeatable(): void
    {
        $this->assertSame(16, $this->fileCount());

        $this->artisan('docs:import')->assertSuccessful();
        $this->assertSame(16, Doc::query()->count());

        $source = app(MarkdownFileSource::class);

        foreach (['nl', 'en'] as $locale) {
            foreach (File::glob(base_path("content/docs/{$locale}/*.md")) as $path) {
                $raw = File::get($path);
                $doc = Doc::query()->where('locale', $locale)->where('slug', basename($path, '.md'))->sole();

                // The file is front matter + body; the body is identical.
                $this->assertStringEndsWith($doc->body, $raw);
                $this->assertSame($source->parse($locale, $doc->slug, $path)['body'], $doc->body);
                $this->assertSame(hash('sha256', $raw), $doc->source_sha256);
            }
        }

        // Second and third run: no duplicates, nothing changed.
        $before = Doc::query()->orderBy('id')->get()->map(fn (Doc $d) => [$d->id, $d->slug, $d->title, $d->body, (string) $d->updated_at])->all();
        $revisions = DocRevision::query()->count();

        foreach ([1, 2] as $run) {
            $report = app(DocImporter::class)->run();
            $this->assertSame([DocImporter::UNCHANGED], array_values(array_unique(array_column($report, 'action'))));
        }

        $this->assertSame(16, Doc::query()->count());
        $this->assertSame($before, Doc::query()->orderBy('id')->get()->map(fn (Doc $d) => [$d->id, $d->slug, $d->title, $d->body, (string) $d->updated_at])->all());
        $this->assertSame($revisions, DocRevision::query()->count());
    }

    public function test_verification_passes_after_import_and_detects_any_difference(): void
    {
        $this->artisan('docs:verify')->assertFailed(); // nothing imported yet

        $this->artisan('docs:import')->assertSuccessful();
        $this->artisan('docs:verify')->assertSuccessful();

        $result = app(DocVerifier::class)->run();
        $this->assertTrue($result['ok']);
        $this->assertSame(['nl' => ['files' => 8, 'records' => 8], 'en' => ['files' => 8, 'records' => 8]], $result['counts']);

        // A single changed character is caught.
        $doc = Doc::query()->where('locale', 'en')->where('slug', 'status-pages')->sole();
        $doc->update(['body' => $doc->body.' ']);

        $result = app(DocVerifier::class)->run();
        $this->assertFalse($result['ok']);
        $failed = collect($result['pages'])->reject->ok->values();
        $this->assertCount(1, $failed);
        $this->assertSame('status-pages', $failed[0]['slug']);
        $this->assertFalse($failed[0]['checks']['body_sha256']);
    }

    public function test_pages_edited_or_deleted_in_the_panel_are_not_overwritten(): void
    {
        $this->artisan('docs:import')->assertSuccessful();

        $edited = Doc::query()->where('locale', 'nl')->where('slug', 'getting-started')->sole();
        $edited->update(['title' => 'Eigen titel']);

        Doc::query()->where('locale', 'nl')->where('slug', 'status-pages')->sole()->delete();

        $report = collect(app(DocImporter::class)->run())->keyBy(fn ($r) => $r['locale'].'/'.$r['slug']);

        $this->assertSame(DocImporter::SKIPPED_EDITED, $report['nl/getting-started']['action']);
        $this->assertSame(DocImporter::SKIPPED_DELETED, $report['nl/status-pages']['action']);
        $this->assertSame('Eigen titel', $edited->fresh()->title);
        $this->assertTrue(Doc::withTrashed()->where('slug', 'status-pages')->where('locale', 'nl')->sole()->trashed());
        $this->assertSame(16, Doc::withTrashed()->count());

        // --force brings the file version back (after confirmation).
        $this->artisan('docs:import', ['--force' => true])
            ->expectsConfirmation('--force overwrites changes made in the admin panel. Continue?', 'yes')
            ->assertSuccessful();
        $this->artisan('docs:verify')->assertSuccessful();
    }

    public function test_site_reads_from_the_database_after_the_import(): void
    {
        $this->get('/nl/docs/getting-started')->assertOk()->assertSee('Aan de slag');

        $this->artisan('docs:import')->assertSuccessful();

        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'getting-started')->sole();
        $doc->update(['title' => 'Snel beginnen', 'body' => $doc->body."\n\n## Nieuwe sectie\n\nTekst uit de database.\n"]);

        $this->get('/nl/docs/getting-started')->assertOk()->assertSee('Snel beginnen')->assertSee('Tekst uit de database.');

        // Drafts and scheduled pages are not public.
        $doc->update(['status' => 'draft']);
        $this->get('/nl/docs/getting-started')->assertNotFound();
        $this->get('/nl/docs')->assertDontSee('Snel beginnen');

        $doc->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get('/nl/docs/getting-started')->assertNotFound();

        $this->travel(2)->days();
        $this->get('/nl/docs/getting-started')->assertOk();
    }

    public function test_pages_follow_the_status_of_their_feature(): void
    {
        $this->artisan('docs:import')->assertSuccessful();
        $feature = Feature::create(['key' => 'status-pages', 'name' => ['nl' => 'Statuspagina\'s', 'en' => 'Status pages'], 'status' => 'available']);
        Doc::query()->where('slug', 'status-pages')->update(['feature_id' => $feature->id]);

        $this->get('/en/docs/status-pages')->assertOk();

        $feature->update(['status' => 'coming_soon']);
        $this->get('/en/docs/status-pages')->assertOk()->assertSee('Coming soon');

        $feature->update(['status' => 'hidden']);
        $this->get('/en/docs/status-pages')->assertNotFound();
        $this->get('/en/docs')->assertDontSee('/en/docs/status-pages', false);
        $this->get('/en/docs?q=status')->assertDontSee('/en/docs/status-pages', false);
    }

    public function test_changing_a_slug_creates_a_permanent_redirect(): void
    {
        $this->artisan('docs:import')->assertSuccessful();
        $doc = Doc::query()->where('locale', 'en')->where('slug', 'api-webhooks')->sole();

        $doc->update(['slug' => 'api']);
        $this->get('/en/docs/api-webhooks')->assertStatus(301)->assertRedirect(url('/en/docs/api'));

        // Renamed again: the oldest URL points straight to the newest (no chain).
        $doc->update(['slug' => 'api-v1']);
        $this->get('/en/docs/api-webhooks')->assertStatus(301)->assertRedirect(url('/en/docs/api-v1'));
        $this->get('/en/docs/api')->assertStatus(301)->assertRedirect(url('/en/docs/api-v1'));
        $this->get('/en/docs/api-v1')->assertOk();

        // Renaming back removes the loop.
        $doc->update(['slug' => 'api-webhooks']);
        $this->get('/en/docs/api-webhooks')->assertOk();
    }

    public function test_every_save_writes_a_revision(): void
    {
        $this->artisan('docs:import')->assertSuccessful();
        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'status-pages')->sole();

        $this->assertSame(['Import uit content/docs/nl/status-pages.md'], $doc->revisions()->pluck('reason')->all());

        $doc->update(['body' => 'Nieuw']);
        $this->assertSame(2, $doc->revisions()->count());
        $this->assertSame('Nieuw', $doc->revisions()->first()->body);
    }
}
