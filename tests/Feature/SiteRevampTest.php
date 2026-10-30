<?php

namespace Tests\Feature;

use App\Models\Doc;
use App\Models\DocFeedback;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanRow;
use App\Models\PlanRowValue;
use App\Models\Setting;
use App\Services\Site\StatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteRevampTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_status_page_shows_the_real_status_and_never_invents_operational(): void
    {
        Http::fake(['serverscope.nl/status/serverscope.json' => Http::response([
            'name' => 'ServerScope', 'overall' => 'degraded', 'generated_at' => '2026-10-30T10:00:00+00:00',
            'components' => [['name' => 'Dashboard', 'state' => 'operational'], ['name' => 'API', 'state' => 'degraded']],
            'incidents' => [['title' => 'Trage API', 'component' => 'API', 'status' => 'investigating', 'started_at' => '2026-10-30T09:00:00+00:00']],
        ])]);

        $this->get('/nl/status')->assertOk()
            ->assertSee('Verminderde prestaties')
            ->assertSee('Dashboard')
            ->assertSee('Trage API')
            ->assertDontSeeText('Alle systemen operationeel');
        $this->getJson('/status.json')->assertOk()->assertExactJson(['state' => 'degraded']);

        Http::assertSent(fn ($request) => $request->url() === 'https://serverscope.nl/status/serverscope.json');
    }

    public function test_status_is_unknown_when_serverscope_cannot_be_reached_or_answers_nonsense(): void
    {
        Http::fake(['*' => Http::response('Bad gateway', 502)]);
        $this->get('/nl/status')->assertOk()->assertSee('Status momenteel onbekend')->assertDontSeeText('operationeel')
            ->assertSee('href="https://serverscope.nl/status/serverscope"', false);

        Cache::flush();
        Http::fake(['*' => Http::response(['overall' => 'all good'])]);
        $this->get('/en/status')->assertOk()->assertSee('Status currently unknown')->assertDontSeeText('operational');

        Cache::flush();
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->assertSame('unknown', app(StatusService::class)->current()['state']);

        // The old hard-coded text is gone from every page.
        Cache::flush();
        foreach (['/nl', '/nl/status', '/en/status'] as $url) {
            $this->get($url)->assertDontSeeText('Alle systemen operationeel')->assertDontSeeText('All systems operational');
        }
    }

    public function test_badge_only_reads_the_cache_and_the_browser_never_calls_serverscope(): void
    {
        Http::fake();

        $html = $this->get('/nl')->assertOk()->getContent();
        Http::assertNothingSent();
        $this->assertStringContainsString('data-status-pending', $html);
        $this->assertStringNotContainsString('serverscope.nl/status/serverscope.json', $html);
        $this->assertStringNotContainsString('serverscope.nl/status/serverscope.json', file_get_contents(resource_path('js/app.js')));

        Cache::put(StatusService::CACHE_KEY, ['state' => 'operational', 'name' => null, 'components' => [], 'incidents' => [], 'generated_at' => null, 'fetched_at' => now()->toIso8601String()], 60);
        $this->get('/nl')->assertSeeText('Alle systemen operationeel')->assertDontSee('data-status-pending', false);
    }

    public function test_pricing_page_shows_placeholders_and_points_to_the_waitlist_while_registration_is_closed(): void
    {
        $this->get('/nl/pricing')->assertOk()
            ->assertSeeInOrder(['Free', 'Pro', 'Ultra'])
            ->assertSee('Prijs volgt')
            ->assertSee('Gratis starten')
            ->assertSee('Registratie is nog niet open')
            ->assertSee('/nl/contact?subject=Wachtlijst%20Pro', false)
            ->assertSee('Vergelijk de abonnementen')
            ->assertSee('Binnenkort');

        Setting::put('waitlist_url', 'https://example.test/wachtlijst');
        $this->get('/en/pricing')->assertSee('href="https://example.test/wachtlijst"', false)->assertSee('Price to follow');

        Setting::put('registration_open', true);
        Setting::put('signup_url', 'https://serverscope.nl/register');
        $pro = Plan::query()->where('key', 'pro')->sole();
        $pro->update(['price_monthly' => 19, 'price_yearly' => 190, 'is_recommended' => true]);
        $servers = PlanRow::query()->where('type', 'limit')->orderBy('sort')->first();
        PlanRowValue::query()->create(['plan_id' => $pro->id, 'plan_row_id' => $servers->id, 'value' => '10']);

        $this->get('/nl/pricing')
            ->assertSee('href="https://serverscope.nl/register"', false)
            ->assertDontSee('Registratie is nog niet open')
            ->assertSee('€ 19')
            ->assertSee('€ 190')
            ->assertSee('Aanbevolen')
            ->assertSee('data-billing-toggle', false);
    }

    public function test_hidden_features_disappear_everywhere_and_coming_soon_is_labelled(): void
    {
        $teams = Feature::query()->where('key', 'teams')->sole();

        $this->get('/nl/features')->assertSee('Teams en rollen')->assertSee('Binnenkort');
        $this->get('/nl/pricing')->assertSee('Teams en rollen');

        $teams->update(['status' => 'hidden']);
        $this->get('/nl/features')->assertDontSee('Teams en rollen');
        $this->get('/nl/pricing')->assertDontSee('Teams en rollen');
        $this->get('/nl')->assertDontSee('Teams en rollen');

        // The install terminal follows its feature.
        Feature::query()->where('key', 'one-command-install')->update(['status' => 'hidden']);
        $this->get('/nl')->assertDontSee('serverscope-install.sh');
        Feature::query()->where('key', 'one-command-install')->update(['status' => 'available']);
        $this->get('/nl')->assertSee("curl --proto '=https' --tlsv1.2 -fsSL https://serverscope.nl/install.sh");
    }

    public function test_home_counters_use_real_numbers(): void
    {
        $available = Feature::query()->where('status', 'available')->count();
        $docs = Doc::query()->visible()->where('locale', 'nl')->count();

        $this->get('/nl')->assertOk()
            ->assertSee('data-count="'.$available.'"', false)
            ->assertSee('data-count="'.$docs.'"', false);
    }

    public function test_search_index_lists_only_visible_pages(): void
    {
        Doc::query()->where('locale', 'en')->where('slug', 'status-pages')->update(['status' => 'draft']);

        $index = $this->getJson('/en/docs/search.json')->assertOk()->json();

        $this->assertCount(7, $index);
        $this->assertNotContains(url('/en/docs/status-pages'), array_column($index, 'u'));
        $this->assertSame(['t', 'd', 'u', 'c', 'x'], array_keys($index[0]));
    }

    public function test_feedback_is_stored_without_personal_data(): void
    {
        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'getting-started')->sole();

        $this->get('/nl/docs/getting-started')->assertSee('Was dit nuttig?');

        $this->postJson('/nl/docs/getting-started/feedback', ['helpful' => true])->assertCreated();
        $this->post('/nl/docs/getting-started/feedback', ['helpful' => '0', 'comment' => '<b>Meer</b> voorbeelden graag'])
            ->assertRedirect(url('/nl/docs/getting-started').'#feedback-title');

        $this->assertSame([true, false], DocFeedback::query()->orderBy('id')->pluck('helpful')->all());
        $this->assertSame('Meer voorbeelden graag', DocFeedback::query()->latest('id')->value('comment'));
        $this->assertSame(['id', 'doc_id', 'helpful', 'comment', 'created_at'], Schema::getColumnListing('doc_feedback'));

        $this->get('/nl/docs/getting-started')->assertSee('Bedankt voor je reactie.');
        $this->postJson('/nl/docs/does-not-exist/feedback', ['helpful' => true])->assertNotFound();
        $this->postJson('/nl/docs/getting-started/feedback', [])->assertUnprocessable();

        $this->assertSame(2, $doc->feedback()->count());
    }

    public function test_contact_subject_is_prefilled_from_the_pricing_buttons(): void
    {
        $this->get('/nl/contact?subject=Wachtlijst%20Pro')->assertOk()->assertSee('value="Wachtlijst Pro"', false);
    }
}
