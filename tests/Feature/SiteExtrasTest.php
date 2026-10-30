<?php

namespace Tests\Feature;

use App\Models\Doc;
use App\Models\Feature;
use App\Models\PageView;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteExtrasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_sitemap_lists_visible_pages_with_hreflang_and_nothing_hidden(): void
    {
        config(['serverscope.noindex' => false]);
        Doc::query()->where('slug', 'bash-agent')->update(['status' => 'draft']);
        Doc::query()->where('slug', 'api-reference')->update(['noindex' => true]);
        Feature::query()->where('key', 'agent-updates')->update(['status' => 'hidden']);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertNotFalse(simplexml_load_string($xml));
        $this->assertStringContainsString('<loc>'.url('/nl/docs/getting-started').'</loc>', $xml);
        $this->assertStringContainsString('hreflang="en" href="'.url('/en/docs/getting-started').'"', $xml);
        $this->assertStringContainsString('<loc>'.url('/en/pricing').'</loc>', $xml);
        $this->assertStringNotContainsString('bash-agent', $xml);
        $this->assertStringNotContainsString('api-reference', $xml);
        $this->assertStringNotContainsString('agent-updates', $xml);

        $this->get('/robots.txt')->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_sitemap_is_empty_where_the_site_is_not_indexed(): void
    {
        config(['serverscope.noindex' => true]);

        $this->assertStringNotContainsString('<url>', $this->get('/sitemap.xml')->getContent());
    }

    public function test_link_check_passes_and_finds_broken_links_and_anchors(): void
    {
        $this->artisan('docs:check-links')->assertSuccessful();

        $doc = Doc::query()->where('locale', 'nl')->where('slug', 'getting-started')->sole();
        $doc->update(['body' => $doc->body."\n\n[Weg](bestaat-niet) en [sectie](server-monitoring#geen-sectie) en [goed](server-monitoring#vereisten)\n"]);

        $this->artisan('docs:check-links')
            ->expectsOutputToContain('bestaat-niet')
            ->expectsOutputToContain('server-monitoring#geen-sectie')
            ->doesntExpectOutputToContain('server-monitoring#vereisten')
            ->assertFailed();
    }

    public function test_page_counter_stores_only_path_day_and_number(): void
    {
        $this->get('/nl/docs')->assertOk();
        $this->get('/nl/docs')->assertOk();
        $this->get('/nl/docs/does-not-exist')->assertNotFound();
        $this->withHeader('User-Agent', 'Googlebot/2.1')->get('/nl/pricing')->assertOk();
        $this->withHeader('Sec-Purpose', 'prefetch')->get('/nl/pricing')->assertOk();

        $this->assertSame(['id', 'path', 'day', 'views'], Schema::getColumnListing('page_views'));
        $this->assertSame([['path' => '/nl/docs', 'views' => 2]], PageView::query()->get(['path', 'views'])->toArray());

        // The counter sets no cookie of its own (the response is the same as without it).
        Setting::put('pageview_counter', false);
        $this->get('/nl/changelog')->assertOk();
        $this->assertSame(0, PageView::query()->where('path', '/nl/changelog')->count());

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Meest bekeken pagina');
        $this->assertSame(0, PageView::query()->where('path', 'like', '/admin%')->count());
    }
}
