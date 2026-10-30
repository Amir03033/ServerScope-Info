<?php

namespace Tests\Feature;

use App\Filament\Resources\PageMeta\Pages\ManagePageMeta;
use App\Models\Doc;
use App\Models\LegalDocument;
use App\Models\PageMeta;
use App\Models\User;
use App\Support\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PublishesLegalDocuments;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use PublishesLegalDocuments;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        // As in production: outside production every page is noindex.
        config(['serverscope.noindex' => false]);
    }

    /**
     * Every public, indexable page: everything in the sitemap.
     *
     * @return list<string>
     */
    private function publicUrls(): array
    {
        preg_match_all('#<loc>([^<]+)</loc>#', $this->get('/sitemap.xml')->assertOk()->getContent(), $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * @return array{title: string, description: ?string, canonical: ?string, robots: ?string}
     */
    private function pageHead(string $url): array
    {
        $html = $this->get($url)->assertOk()->getContent();
        $find = fn (string $pattern) => preg_match($pattern, $html, $m) ? html_entity_decode($m[1], ENT_QUOTES) : null;

        return [
            'title' => trim((string) $find('#<title>(.*?)</title>#s')),
            'description' => $find('#<meta name="description" content="([^"]*)"#'),
            'canonical' => $find('#<link rel="canonical" href="([^"]*)"#'),
            'robots' => $find('#<meta name="robots" content="([^"]*)"#'),
        ];
    }

    public function test_every_public_page_has_a_unique_title_a_description_and_a_canonical(): void
    {
        $this->fillCompany();
        foreach (array_keys(LegalDocument::DOCUMENTS) as $key) {
            $this->publish($key, 'nl');
            $this->publish($key, 'en');
        }

        $urls = $this->publicUrls();
        $this->assertGreaterThan(40, count($urls), 'fixed pages, docs and legal pages are in the sitemap');
        $this->assertContains(url('/nl/privacy'), $urls);
        $this->assertContains(url('/en/waitlist'), $urls);

        $titles = [];
        $descriptions = [];

        foreach ($urls as $url) {
            $head = $this->pageHead(parse_url($url, PHP_URL_PATH));

            $this->assertNotSame('', $head['title'], "{$url} has no title");
            $this->assertNotSame('ServerScope', $head['title'], "{$url} only has the default title");
            $this->assertNotEmpty($head['description'], "{$url} has no meta description");
            $this->assertSame($url, $head['canonical'], "{$url} has no (or a wrong) canonical");
            $this->assertNull($head['robots'], "{$url} is in the sitemap but not indexable");

            // Unique within a language; translations are linked with hreflang.
            $locale = explode('/', trim((string) parse_url($url, PHP_URL_PATH), '/'))[0];
            $titles[$locale.' '.$head['title']][] = $url;
            $descriptions[$locale.' '.$head['description']][] = $url;
        }

        $this->assertSame([], array_filter($titles, fn ($list) => count($list) > 1), 'Titles must be unique');
        $this->assertSame([], array_filter($descriptions, fn ($list) => count($list) > 1), 'Descriptions must be unique');
    }

    public function test_title_and_description_come_from_the_admin_panel_when_set(): void
    {
        $this->assertSame(__('site.pricing.title', [], 'en'), $this->pageHead('/en/pricing')['title']);

        PageMeta::query()->create(['page' => 'pricing', 'locale' => 'en', 'title' => 'Plans and prices for server monitoring — ServerScope', 'description' => 'Own description']);
        Seo::flush();

        $head = $this->pageHead('/en/pricing');
        $this->assertSame('Plans and prices for server monitoring — ServerScope', $head['title']);
        $this->assertSame('Own description', $head['description']);
        $this->assertSame(__('site.pricing.title', [], 'nl'), $this->pageHead('/nl/pricing')['title'], 'the other language keeps its default');
    }

    public function test_a_page_marked_noindex_gets_robots_and_leaves_the_sitemap(): void
    {
        PageMeta::query()->create(['page' => 'changelog', 'locale' => 'nl', 'noindex' => true]);
        Seo::flush();

        $this->assertSame('noindex, nofollow', $this->pageHead('/nl/changelog')['robots']);
        $this->assertNull($this->pageHead('/en/changelog')['robots']);
        $this->assertNotContains(url('/nl/changelog'), $this->publicUrls());
        $this->assertContains(url('/en/changelog'), $this->publicUrls());
    }

    public function test_docs_search_results_are_not_indexed_but_links_are_followed(): void
    {
        $this->assertNull($this->pageHead('/en/docs')['robots']);
        $this->assertSame('noindex, follow', $this->pageHead('/en/docs?q=agent')['robots']);
    }

    public function test_hreflang_pairs_and_x_default(): void
    {
        $html = $this->get('/en/features')->getContent();

        $this->assertStringContainsString('<link rel="alternate" hreflang="nl" href="'.url('/nl/features').'">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="en" href="'.url('/en/features').'">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="x-default" href="'.url('/nl/features').'">', $html);
    }

    public function test_the_404_page_is_friendly_in_both_languages_and_not_indexed(): void
    {
        $this->get('/en/does-not-exist')->assertNotFound()
            ->assertSee(__('site.errors.headline', [], 'en'))
            ->assertSee('action="'.url('en/docs').'"', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get('/nl/bestaat-niet')->assertNotFound()->assertSee(__('site.errors.headline', [], 'nl'));
        $this->get('/nl/docs/bestaat-niet')->assertNotFound()->assertSee(__('site.errors.headline', [], 'nl'));
    }

    public function test_the_admin_panel_shows_all_fixed_pages_with_a_length_indicator(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/seo')->assertOk();
        $this->assertSame(count(PageMeta::PAGES) * 2, PageMeta::query()->count());

        $this->assertSame('Leeg: de standaardtekst wordt gebruikt', Seo::lengthHint('', Seo::TITLE));
        $this->assertStringContainsString('goed', Seo::lengthHint(str_repeat('a', 50), Seo::TITLE));
        $this->assertStringContainsString('te lang', Seo::lengthHint(str_repeat('a', 61), Seo::TITLE));
        $this->assertSame('warning', Seo::lengthColor(str_repeat('a', 200), Seo::DESCRIPTION));
        $this->assertTrue(class_exists(ManagePageMeta::class));
        $this->assertGreaterThan(0, Doc::query()->count());
    }
}
