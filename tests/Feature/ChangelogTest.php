<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Release;
use App\Services\Site\ChangelogImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangelogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_existing_changelog_is_imported_once_with_drafts_unpublished(): void
    {
        $this->artisan('site:defaults')->assertSuccessful();
        $this->artisan('site:defaults')->assertSuccessful();

        // 0.1.0 + 26 October + 2 drafts, per locale, no duplicates.
        $this->assertSame(8, Release::query()->count());
        $this->assertSame(['draft', 'draft'], Release::query()->where('locale', 'nl')->where('status', 'draft')->pluck('status')->map->value->all());

        // The 26 October body is the ServerScope changelog, unchanged after its heading.
        $source = file_get_contents(base_path('content/changelog/nl/2026-10-26-website-monitoring.md'));
        $release = Release::query()->where('locale', 'nl')->where('source_key', 'file:2026-10-26-website-monitoring')->sole();
        $this->assertStringEndsWith($release->body, $source);
        $this->assertStringContainsString('Monitoring per pagina', $release->body);

        $this->get('/nl/changelog')->assertOk()
            ->assertSee('Websitemonitoring, slimme incidenten en voorspellingen')
            ->assertSee('Monitoring per pagina')
            ->assertSee('v0.1.0')
            ->assertSee('Agentverbinding via HTTPS', false)
            ->assertDontSee('Teams en rollen')
            ->assertDontSee('met één commando');

        $this->get('/en/changelog')->assertOk()->assertSee('Website monitoring, smarter incidents and forecasts')->assertDontSee('Teams and roles');
    }

    public function test_entries_edited_in_the_panel_are_kept_and_hidden_features_hide_entries(): void
    {
        $this->artisan('site:defaults')->assertSuccessful();

        $draft = Release::query()->where('locale', 'nl')->where('source_key', 'file:draft-teams')->sole();
        $draft->update(['status' => 'published', 'body' => $draft->body."\nEigen toevoeging.\n"]);

        $report = collect(app(ChangelogImporter::class)->run())->firstWhere('key', 'file:draft-teams');
        $this->assertSame('overgeslagen: bewerkt in Filament', $report['action']);
        $this->get('/nl/changelog')->assertSee('Teams en rollen')->assertSee('Eigen toevoeging.');

        Feature::query()->where('key', 'teams')->update(['status' => 'hidden']);
        $this->get('/nl/changelog')->assertDontSee('Teams en rollen');
    }
}
