<?php

namespace Tests\Feature;

use App\Models\Doc;
use App\Models\DocRevision;
use App\Services\Docs\DocImporter;
use App\Services\Docs\DraftRevisionImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftRevisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_a_fresh_database_gets_the_current_pages_without_redundant_drafts(): void
    {
        $this->assertSame(0, DocRevision::query()->where('is_draft', true)->count());
        $this->artisan('docs:verify')->assertSuccessful();

        $this->get('/en/docs/security-account')->assertSee('depends on its <strong>plan</strong>', false)->assertDontSee('has no subscriptions or plans');
        $this->get('/nl/docs/getting-started')->assertSee('Zelf aanmelden')->assertSee('Eigenaar')->assertDontSee('geen openbare registratie');
        $this->get('/en/docs/getting-started')->assertSee('Sign up yourself')->assertDontSee('These English guides quote the Dutch');
    }

    public function test_a_page_edited_in_the_panel_receives_the_update_as_a_draft(): void
    {
        // As on production: the teams draft was published in the panel earlier.
        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'security-account')->sole();
        $doc->update(['body' => "# Beveiliging en accountbeheer\n\nEr zijn nog geen abonnementen actief.\n"]);
        $this->assertTrue($doc->fresh()->editedSinceImport());

        $this->assertContains('overgeslagen: na import bewerkt in Filament', array_column(app(DocImporter::class)->run(), 'action'));
        app(DraftRevisionImporter::class)->run();
        app(DraftRevisionImporter::class)->run(); // repeatable

        $draft = $doc->fresh()->pendingDrafts()->sole();
        $this->assertSame(DraftRevisionImporter::REASON, $draft->reason);
        $this->assertStringContainsString('Escalatiecontacten', $draft->body);
        $this->get('/nl/docs/security-account')->assertSee('Er zijn nog geen abonnementen actief');

        $doc->fresh()->publishDraft($draft);
        $this->get('/nl/docs/security-account')->assertSee('Escalatiecontacten')->assertDontSee('Er zijn nog geen abonnementen actief');
        $this->assertSame(0, $doc->fresh()->pendingDrafts()->count());
    }

    public function test_bash_agent_guide_asks_customers_to_get_in_touch_and_names_no_download(): void
    {
        $this->get('/nl/docs/bash-agent')->assertOk()->assertSee('href="/nl/contact"', false)->assertSee('Er is nog geen openbare download');
        $this->get('/en/docs/bash-agent')->assertSee('There is no public download yet');
        $this->assertStringNotContainsString('agent/bash', Doc::query()->where('slug', 'bash-agent')->where('locale', 'nl')->value('body'));
    }
}
