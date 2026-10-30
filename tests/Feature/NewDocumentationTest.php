<?php

namespace Tests\Feature;

use App\Models\Doc;
use App\Models\Feature;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class NewDocumentationTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'teams-and-roles' => 'teams',
        'connect-server' => 'one-command-install',
        'service-checks' => 'service-checks',
        'bash-agent' => 'bash-agent',
        'agent-updates' => 'agent-updates',
        'agent-security' => null,
        'api-reference' => 'api',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_every_new_page_exists_in_both_languages_linked_to_its_feature(): void
    {
        foreach (['nl', 'en'] as $locale) {
            $this->assertSame(array_keys(self::PAGES), array_values(array_intersect(array_keys(self::PAGES), Doc::query()->where('locale', $locale)->pluck('slug')->all())));

            foreach (self::PAGES as $slug => $feature) {
                $doc = Doc::query()->where('locale', $locale)->where('slug', $slug)->sole();
                $this->assertSame($feature, $doc->feature?->key, "{$locale}/{$slug}");
                $this->assertNotNull($doc->doc_category_id);
                $this->assertSame(hash('sha256', $doc->body), $doc->imported_body_sha256);
            }
        }

        // Re-running creates no duplicates; the original 16 pages still verify.
        $this->artisan('site:defaults')->assertSuccessful();
        $this->assertSame(16 + 2 * count(self::PAGES), Doc::query()->count());
        $this->artisan('docs:verify')->assertSuccessful();
    }

    public function test_coming_soon_pages_are_labelled_and_hidden_pages_disappear(): void
    {
        // Every feature starts as available; the owner sets coming soon per feature.
        $this->get('/nl/docs/agent-updates')->assertOk()->assertDontSee('Deze functie is nog niet beschikbaar');
        Feature::query()->whereIn('key', ['agent-updates', 'one-command-install'])->update(['status' => 'coming_soon']);

        $this->get('/nl/docs/agent-updates')->assertOk()->assertSee('Binnenkort')->assertSee('Deze functie is nog niet beschikbaar');
        $this->get('/en/docs/connect-server')->assertOk()->assertSee('Coming soon');
        $this->get('/nl/docs/agent-security')->assertOk()->assertDontSee('Deze functie is nog niet beschikbaar');

        Feature::query()->where('key', 'agent-updates')->update(['status' => 'hidden']);
        $this->get('/nl/docs/agent-updates')->assertNotFound();
        $this->get('/nl/docs')->assertDontSee('/nl/docs/agent-updates', false);

        Feature::query()->where('key', 'agent-updates')->update(['status' => 'available']);
        $this->get('/nl/docs/agent-updates')->assertOk()->assertDontSee('Deze functie is nog niet beschikbaar');
    }

    public function test_release_fingerprint_placeholder_is_filled_from_the_settings(): void
    {
        $this->get('/nl/docs/agent-updates')->assertSee('(volgt zodra de releasesleutel is aangemaakt)');
        $this->get('/en/docs/agent-updates')->assertSee('(to follow once the release key has been created)');

        Setting::put('release_fingerprint', str_repeat('3f', 32));
        $this->get('/nl/docs/agent-updates')->assertSee(str_repeat('3f', 32))->assertDontSee('{release_fingerprint}');
    }

    public function test_new_pages_contain_the_real_commands_and_no_secrets(): void
    {
        $this->get('/nl/docs/connect-server')->assertOk()
            ->assertSeeText('serverscope-install.sh --uninstall')
            ->assertSee('Ubuntu 22.04')
            ->assertSee('SERVERSCOPE_ENROLL_TOKEN');
        $this->get('/en/docs/agent-updates')->assertSeeText('--disable-updates')->assertSeeText('serverscope-updater --status');

        foreach (File::allFiles(base_path('content/docs-revamp')) as $file) {
            $content = File::get($file->getPathname());
            $this->assertDoesNotMatchRegularExpression('~\b(ssk|sse|whsec)_[A-Za-z0-9]{8,}~', $content, $file->getFilename());
        }
    }
}
