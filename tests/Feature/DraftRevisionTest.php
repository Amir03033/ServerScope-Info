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

    public function test_drafts_exist_for_both_pages_without_changing_the_published_content(): void
    {
        foreach (['nl', 'en'] as $locale) {
            foreach (['getting-started', 'security-account'] as $slug) {
                $doc = Doc::query()->where('locale', $locale)->where('slug', $slug)->sole();
                $draft = $doc->pendingDrafts()->sole();

                $this->assertTrue($draft->is_draft);
                $this->assertNotSame($doc->body, $draft->body);
                $this->assertSame(hash('sha256', $doc->body), $doc->imported_body_sha256);
            }
        }

        $this->artisan('docs:verify')->assertSuccessful();

        // Published pages still say what they said; drafts are not public.
        $this->get('/nl/docs/security-account')->assertSee('ServerScope heeft geen abonnementen of pakketten')->assertDontSee('Er zijn nog geen abonnementen actief');
        $this->get('/en/docs/getting-started')->assertSee('ServerScope has two roles')->assertDontSee('There are three roles');

        // Repeatable, and drafts do not count as "edited in the panel".
        app(DraftRevisionImporter::class)->run();
        $this->assertSame(4, DocRevision::query()->where('is_draft', true)->count());
        $this->assertSame(['ongewijzigd'], array_values(array_unique(array_column(app(DocImporter::class)->run(), 'action'))));
    }

    public function test_drafts_match_the_new_roles_and_plans(): void
    {
        $nl = Doc::query()->where('locale', 'nl')->where('slug', 'getting-started')->sole()->pendingDrafts()->sole()->body;
        $this->assertStringContainsString('| | Lid | Beheerder | Eigenaar |', $nl);
        $this->assertStringContainsString('[Teams en rollen](teams-and-roles)', $nl);

        $security = Doc::query()->where('locale', 'en')->where('slug', 'security-account')->sole()->pendingDrafts()->sole()->body;
        $this->assertStringContainsString('No subscriptions are active yet', $security);
        $this->assertStringNotContainsString('has no subscriptions or plans', $security);
    }

    public function test_publishing_a_draft_makes_it_live_and_can_be_rolled_back(): void
    {
        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'security-account')->sole();
        $original = $doc->body;

        $doc->publishDraft($doc->pendingDrafts()->sole());

        $this->get('/nl/docs/security-account')->assertSee('Er zijn nog geen abonnementen actief');
        $this->assertSame(0, $doc->pendingDrafts()->count());
        $this->assertStringStartsWith('Concept gepubliceerd', $doc->revisions()->where('is_draft', false)->first()->reason);
        $this->assertTrue($doc->revisions()->where('is_draft', false)->get()->contains(fn ($r) => $r->body === $original));
    }

    public function test_bash_agent_guide_asks_customers_to_get_in_touch_and_names_no_download(): void
    {
        $this->get('/nl/docs/bash-agent')->assertOk()->assertSee('href="/nl/contact"', false)->assertSee('Er is nog geen openbare download');
        $this->get('/en/docs/bash-agent')->assertSee('There is no public download yet');
        $this->assertStringNotContainsString('agent/bash', Doc::query()->where('slug', 'bash-agent')->where('locale', 'nl')->value('body'));
    }
}
