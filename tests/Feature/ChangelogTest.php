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

    public function test_existing_changelog_is_imported_once_and_published(): void
    {
        $this->artisan('site:defaults')->assertSuccessful();
        $this->artisan('site:defaults')->assertSuccessful();

        // 0.1.0 + ten Markdown entries, per locale, no duplicates.
        $this->assertSame(22, Release::query()->count());
        $this->assertSame(0, Release::query()->where('status', 'draft')->count());

        // Every entry has its own day, newest first.
        $dates = Release::query()->where('locale', 'en')->orderByDesc('released_at')->pluck('released_at')->map->toDateString()->all();
        $this->assertSame(array_values(array_unique($dates)), $dates);
        $this->assertSame('2026-11-04', $dates[0]);

        // The 26 October body is the ServerScope changelog, unchanged after its heading.
        $source = file_get_contents(base_path('content/changelog/nl/2026-10-26-website-monitoring.md'));
        $release = Release::query()->where('locale', 'nl')->where('source_key', 'file:2026-10-26-website-monitoring')->sole();
        $this->assertStringEndsWith($release->body, $source);
        $this->assertStringContainsString('Monitoring per pagina', $release->body);
        $this->assertStringNotContainsString('voorlopig alleen in het Nederlands', $release->body);

        $this->get('/nl/changelog')->assertOk()
            ->assertSeeInOrder(['Automatische deploys, logs', 'Missiecontrole', 'NEXUS', 'Time Machine', 'Abonnementen', 'Nieuw ontwerp', 'Agent-updates', 'Een server koppelen met één commando', 'Teams en rollen', 'Websitemonitoring, slimme incidenten en voorspellingen'])
            ->assertSee('Monitoring per pagina')
            ->assertSee('v0.1.0')
            ->assertSee('Agentverbinding via HTTPS', false);

        $this->get('/en/changelog')->assertOk()
            ->assertSee('Automatic deployments, logs, postmortems, scheduled jobs and chat channels (agent 0.4.0)')
            ->assertSee('Website monitoring, smarter incidents and forecasts')
            ->assertSee('Teams and roles');
    }

    public function test_entries_typed_into_the_panel_are_tidied_by_the_migration(): void
    {
        $migration = require database_path('migrations/2026_11_04_100000_tidy_changelog_entries.php');
        $item = ['type' => 'new', 'text' => 'Meldingen per e-mail en in de app, met eigen voorkeuren per gebruiker.'];

        $v2 = Release::query()->create(['locale' => 'nl', 'version' => '0.2.0', 'released_at' => '2026-10-26', 'items' => [['type' => 'new', 'text' => 'Domeinmonitoring'], $item, $item], 'status' => 'published']);
        $v3 = Release::query()->create(['locale' => 'nl', 'version' => '0.3.0', 'released_at' => '2026-10-26', 'items' => [$item], 'status' => 'published']);
        $v4 = Release::query()->create(['locale' => 'nl', 'version' => '0.4.0', 'released_at' => '2026-10-28', 'items' => [$item], 'status' => 'published']);

        $migration->up();
        $migration->up(); // repeatable

        $this->assertSame([['type' => 'new', 'text' => 'Domeinmonitoring'], $item], $v2->fresh()->items);
        $this->assertSame('2026-10-15', $v2->fresh()->released_at->toDateString());
        $this->assertSame('2026-10-21', $v3->fresh()->released_at->toDateString());
        $this->assertSame('draft', $v4->fresh()->status->value);
        $this->assertSame([$item], $v3->fresh()->items);
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
