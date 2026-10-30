<?php

namespace Tests\Feature;

use App\Enums\FeatureStatus;
use App\Filament\Resources\ContentPages\Pages\EditContentPage;
use App\Models\Doc;
use App\Models\Page;
use App\Models\User;
use App\Services\DocumentationService;
use App\Services\PageImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        config(['serverscope.noindex' => false]);
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_all_approved_pages_exist_in_both_languages(): void
    {
        $this->assertSame(count(Page::PAGES) * 2, Page::query()->where('status', 'published')->count());

        foreach (Page::PAGES as $key => [$nl, $en]) {
            foreach (['nl' => $nl, 'en' => $en] as $locale => $slug) {
                $page = Page::query()->where('key', $key)->where('locale', $locale)->sole();
                $this->assertSame($slug, $page->slug);
                $this->assertNotEmpty($page->summary, "{$locale}/{$slug} has a summary");
                $this->assertNotEmpty($page->seo_description);

                $this->get("/{$locale}/{$slug}")->assertOk()
                    ->assertSee($page->title)
                    ->assertSee(trans('site.pages.cta_title', [], $locale))
                    ->assertSee('class="callout callout-summary"', false);
            }
        }

        $this->get('/nl/wat-is-serverscope')->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/what-is-serverscope').'">', false);
    }

    public function test_every_documentation_link_on_the_pages_exists(): void
    {
        $slugs = Doc::query()->visible()->get(['locale', 'slug'])->map(fn ($d) => "/{$d->locale}/docs/{$d->slug}")->all();

        foreach (Page::query()->get() as $page) {
            preg_match_all('#\]\((/(?:nl|en)/[^)\s]*)\)#', $page->body.json_encode($page->faq), $links);
            $this->assertNotEmpty($links[1], "{$page->path()} links to the documentation");

            foreach ($links[1] as $link) {
                if (str_contains($link, '/docs/')) {
                    $this->assertContains($link, $slugs, "{$page->path()} links to a missing doc: {$link}");
                } else {
                    $this->get($link)->assertOk();
                }
            }
        }
    }

    public function test_no_invented_claims_or_competitor_names(): void
    {
        foreach (Page::query()->get() as $page) {
            $text = mb_strtolower($page->title.' '.$page->summary.' '.$page->body.' '.json_encode($page->faq));

            $this->assertDoesNotMatchRegularExpression('/\d+([.,]\d+)?\s?%/', $text, "{$page->path()}: no percentages");
            $this->assertDoesNotMatchRegularExpression('/€|\$\d|\beur\b|per maand|per month/', $text, "{$page->path()}: no prices");
            $this->assertDoesNotMatchRegularExpression('/\b(review|beoordeling|klanten vertrouwen|customers trust|sterren|stars)\b/', $text, "{$page->path()}: no reviews");
            $this->assertDoesNotMatchRegularExpression('/uptimerobot|pingdom|datadog|zabbix|nagios|better ?stack|statuscake|hetrix|new relic|grafana cloud/', $text, "{$page->path()}: no competitor names");
        }
    }

    public function test_faq_is_visible_and_matches_the_faqpage_data(): void
    {
        $html = $this->get('/en/vps-monitoring')->getContent();
        $page = Page::query()->where('key', 'vps')->where('locale', 'en')->sole();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $faq = collect(json_decode($m[1], true)['@graph'])->firstWhere('@type', 'FAQPage');
        $this->assertCount(count($page->faq), $faq['mainEntity']);

        foreach ($faq['mainEntity'] as $question) {
            $this->assertStringContainsString(e($question['name']), $html, 'every question in the data is on the page');
        }

        $glossary = $this->get('/en/glossary')->getContent();
        $this->assertStringNotContainsString('FAQPage', $glossary, 'no FAQPage without visible questions');
        $this->assertStringContainsString('"SoftwareApplication"', $this->get('/en/what-is-serverscope')->getContent());
    }

    public function test_bots_get_exactly_the_same_html_as_browsers(): void
    {
        $normalise = fn (string $html) => preg_replace('/(name="_token" value="|content=")[A-Za-z0-9]{40}"/', '$1"', $html);

        foreach (['/nl/vps-monitoren', '/en/compare', '/en/docs/getting-started', '/nl'] as $path) {
            $browser = $this->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) Firefox/131.0')->get($path)->getContent();

            foreach (['GPTBot/1.2', 'ClaudeBot/1.0', 'Googlebot/2.1', 'PerplexityBot/1.0'] as $bot) {
                $this->assertSame($normalise($browser), $normalise($this->withHeader('User-Agent', "Mozilla/5.0 (compatible; {$bot})")->get($path)->getContent()), "{$path} for {$bot}");
            }
        }
    }

    public function test_a_page_follows_the_status_of_its_feature(): void
    {
        $page = Page::query()->where('key', 'dns')->where('locale', 'en')->sole();

        $page->feature->update(['status' => FeatureStatus::ComingSoon]);
        $this->get('/en/dns-change-monitoring')->assertOk()->assertSee(trans('site.pages.coming_soon', [], 'en'));
        $this->assertStringNotContainsString('dns-change-monitoring', $this->get('/llms.txt')->getContent());

        $page->feature->update(['status' => FeatureStatus::Hidden]);
        $this->get('/en/dns-change-monitoring')->assertNotFound();
        $this->get('/en/dns-change-monitoring.md')->assertNotFound();
        $this->assertStringNotContainsString('dns-change-monitoring', $this->get('/sitemap.xml')->getContent());

        $page->update(['status' => 'draft']);
        $page->feature->update(['status' => FeatureStatus::Available]);
        $this->get('/en/dns-change-monitoring')->assertNotFound();
    }

    public function test_edits_in_the_panel_are_kept_and_make_revisions(): void
    {
        $this->actingAs($this->user('editor'));
        $page = Page::query()->where('key', 'uptime')->where('locale', 'nl')->sole();

        $this->get('/admin/content-pages')->assertOk();

        Livewire::test(EditContentPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['summary' => 'Een eigen samenvatting.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Een eigen samenvatting.', $page->fresh()->summary);
        $this->assertSame(2, $page->revisions()->count());

        app(PageImporter::class)->run();
        $this->assertSame('Een eigen samenvatting.', $page->fresh()->summary, 'importing again never overwrites');

        $this->get('/nl/uptime-monitoring')->assertSee('Een eigen samenvatting.');
        $this->get('/admin-preview/page/'.$page->id)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        auth()->logout();
        $this->get('/admin-preview/page/'.$page->id)->assertForbidden();
    }

    public function test_docs_get_an_optional_in_short_box_that_is_empty_for_existing_pages(): void
    {
        $this->assertSame(0, Doc::query()->whereNotNull('summary')->count(), 'existing docs are unchanged');
        $this->get('/en/docs/getting-started')->assertDontSee('callout-summary', false);

        Doc::query()->where('locale', 'en')->where('slug', 'getting-started')->update(['summary' => 'ServerScope monitors servers and domains.']);
        app(DocumentationService::class)->flush();

        $this->get('/en/docs/getting-started')->assertSee('callout-summary', false)->assertSee('ServerScope monitors servers and domains.')
            ->assertSee('This is a summary. The full text below applies.');
        $this->get('/en/docs/getting-started.md')->assertSee("> [!SUMMARY]\n> ServerScope monitors servers and domains.", false);
    }
}
