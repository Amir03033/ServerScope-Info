<?php

namespace Tests\Feature;

use App\Filament\Resources\Legal\Pages\EditLegalDocument;
use App\Filament\Resources\Legal\RelationManagers\VersionsRelationManager;
use App\Models\LegalDocument;
use App\Models\LegalVersion;
use App\Models\Setting;
use App\Models\User;
use App\Services\Legal\CompanyData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    private function fillCompany(): void
    {
        foreach (array_keys(CompanyData::FIELDS) as $key) {
            if (! str_starts_with($key, 'legal.') && ! str_starts_with($key, 'retention.') && $key !== 'company.same_as' && $key !== 'company.phone') {
                Setting::put($key, match ($key) {
                    'company.vat_exempt' => false,
                    'company.audience' => 'business',
                    'security_txt.expires' => now()->addMonths(6)->toDateString(),
                    default => 'Waarde '.$key,
                });
            }
        }

        Setting::put('retention.waitlist', 'tot registratie opengaat');
        Setting::put('retention.contact', '12 maanden');
    }

    private function publish(string $key, string $locale = 'nl'): LegalVersion
    {
        $version = LegalDocument::query()->where('key', $key)->where('locale', $locale)->sole()->versions()->sole();
        $version->update(['status' => 'published', 'effective_at' => today(), 'version' => '1.0']);

        return $version;
    }

    public function test_seven_drafts_per_language_exist_and_nothing_is_public_yet(): void
    {
        $this->assertSame(14, LegalVersion::query()->where('status', 'draft')->count());

        foreach (['privacy', 'cookies', 'colofon', 'voorwaarden', 'verwerkersovereenkomst', 'acceptabel-gebruik', 'responsible-disclosure'] as $slug) {
            $this->get('/nl/'.$slug)->assertNotFound();
        }

        $this->get('/nl')->assertDontSee(__('site.footer.legal_title', [], 'nl').'</h2>', false);
    }

    public function test_publishing_is_blocked_until_the_company_details_are_complete(): void
    {
        $version = LegalDocument::query()->where('key', 'imprint')->where('locale', 'nl')->sole()->versions()->sole();

        try {
            $version->update(['status' => 'published', 'effective_at' => today()]);
            $this->fail('Publishing should be blocked');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('KvK-nummer', collect($e->errors())->flatten()->implode(' '));
        }

        $this->assertSame('draft', $version->fresh()->status);

        $this->fillCompany();
        $version->refresh()->update(['status' => 'published', 'effective_at' => today()]);
        $this->assertSame('published', $version->fresh()->status);
        $this->assertNotNull($version->fresh()->published_at);
    }

    public function test_published_page_fills_placeholders_hides_lawyer_notes_and_has_summaries(): void
    {
        $this->fillCompany();
        $this->publish('privacy');
        $this->publish('privacy', 'en');

        $html = $this->get('/nl/privacy')->assertOk()
            ->assertSee('Privacyverklaring')
            ->assertSee('Waarde company.name')
            ->assertSee('In het kort')
            ->assertSee('Dit is een samenvatting. De volledige tekst hieronder geldt.')
            ->assertSee('Geldt vanaf')
            ->assertSee('data-print', false)
            ->assertSee('data-toc', false)
            ->assertSee('id="1-wie-wij-zijn"', false)
            ->assertDontSee('JURIST')
            ->assertDontSee('Concept: nog niet juridisch gecontroleerd')
            ->assertSee('hreflang="en" href="'.url('/en/privacy').'"', false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/\{(company|legal|retention|cookie)\.[a-z_]+\}/', $html);
        $this->assertStringContainsString('geen automatische termijn', $html); // empty retention fields say so

        // Footer link and the privacy note at the forms.
        config(['serverscope.contact_mail_to' => 'info@example.test']);
        $this->get('/nl/waitlist')->assertSee('privacyverklaring</a>', false);
        $this->get('/nl/contact')->assertSee('privacyverklaring</a>', false);
        $this->get('/nl')->assertSee('href="'.url('/nl/privacy').'"', false);
    }

    public function test_versions_history_and_upcoming_version(): void
    {
        $this->fillCompany();
        $v1 = $this->publish('terms');
        $v1->update(['effective_at' => today()->subMonth()]);

        $document = $v1->document;
        $v2 = $document->versions()->create(['version' => '1.1', 'body' => $v1->body."\n\nNieuw artikel.", 'changelog' => 'Artikel toegevoegd.', 'status' => 'draft']);
        $v2->update(['status' => 'published', 'effective_at' => today()->addMonth()]);

        $this->get('/nl/voorwaarden')->assertOk()->assertSee('Vanaf')->assertSee('Lees de nieuwe versie')->assertDontSee('Nieuw artikel.');
        $this->get('/nl/voorwaarden/versies')->assertOk()->assertSee('Versie 1.0')->assertSee('Versie 1.1')->assertSee('Artikel toegevoegd.');
        $this->get('/nl/voorwaarden/versie/1.1')->assertOk()->assertSee('Nieuw artikel.');
        $this->get('/nl/voorwaarden/versie/9.9')->assertNotFound();
    }

    public function test_security_txt_follows_rfc_9116(): void
    {
        $this->get('/.well-known/security.txt')->assertNotFound();

        $this->fillCompany();
        Setting::put('company.security_email', 'security@example.test');
        $this->publish('disclosure');

        $body = $this->get('/.well-known/security.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();
        $this->assertMatchesRegularExpression('/^Contact: mailto:security@example\.test$/m', $body);
        $this->assertMatchesRegularExpression('/^Expires: \d{4}-\d{2}-\d{2}T23:59:59Z$/m', $body);
        $this->assertStringContainsString('Preferred-Languages: nl, en', $body);
        $this->assertStringContainsString('Canonical: '.url('/.well-known/security.txt'), $body);
        $this->assertStringContainsString('Policy: '.url('/nl/responsible-disclosure'), $body);

        Setting::put('security_txt.expires', now()->subDay()->toDateString());
        $this->get('/.well-known/security.txt')->assertNotFound();
    }

    public function test_admin_panel_shows_the_draft_banner_and_blocks_publishing(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $document = LegalDocument::query()->where('key', 'privacy')->where('locale', 'nl')->sole();
        $this->get('/admin/legal')->assertOk()->assertSee('Privacyverklaring');
        $this->get("/admin/legal/{$document->id}/edit")->assertOk();

        $version = $document->versions()->sole();
        Livewire::test(VersionsRelationManager::class, ['ownerRecord' => $document, 'pageClass' => EditLegalDocument::class])
            ->mountTableAction('edit', $version)
            ->assertMountedActionModalSee('Concept: nog niet juridisch gecontroleerd')
            ->assertMountedActionModalSee('Publiceren is nog geblokkeerd');

        Livewire::test(VersionsRelationManager::class, ['ownerRecord' => $document, 'pageClass' => EditLegalDocument::class])
            ->callTableAction('publish', $version);
        $this->assertSame('draft', $version->fresh()->status);

        $this->get(route('legal.preview', $version))->assertOk()->assertSee('Concept: nog niet juridisch gecontroleerd');

        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $this->actingAs($editor)->get('/admin/legal')->assertForbidden();
        $this->actingAs($editor)->get(route('legal.preview', $version))->assertForbidden();
    }

    public function test_every_draft_only_uses_known_placeholders(): void
    {
        $known = array_keys(app(CompanyData::class)->placeholders('nl'));

        foreach (File::allFiles(base_path('content/legal')) as $file) {
            preg_match_all('/\{[a-z_]+\.[a-z_]+\}/', File::get($file->getPathname()), $m);

            foreach (array_unique($m[0]) as $placeholder) {
                $this->assertContains($placeholder, $known, $file->getRelativePathname().' uses '.$placeholder);
            }
        }
    }

    public function test_sessions_store_no_visitor_ip_or_user_agent_by_default(): void
    {
        $this->assertStringContainsString("env('SESSION_DRIVER', 'file')", file_get_contents(config_path('session.php')));
        $this->assertMatchesRegularExpression('/^SESSION_DRIVER=file$/m', file_get_contents(base_path('.env.example')));
    }
}
