<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function company(): void
    {
        config(['serverscope.company' => [
            'name' => 'OnHolland Test BV', 'kvk' => '12345678', 'vat_id' => 'NL123456789B01',
            'address' => 'Teststraat 1, 1234 AB Plaats', 'email' => 'legal@example.test', 'hosting_provider' => 'Voorbeeld Hosting',
        ], 'serverscope.legal_updated' => '2026-11-01']);
    }

    public function test_both_pages_render_in_both_languages_with_the_company_details(): void
    {
        $this->company();

        foreach (['nl' => ['Algemene voorwaarden', 'Privacyverklaring'], 'en' => ['Terms and conditions', 'Privacy statement']] as $locale => [$terms, $privacy]) {
            $this->get("/{$locale}/terms")->assertOk()->assertSee($terms)->assertSee('OnHolland Test BV')->assertSee('12345678')->assertSee('legal@example.test')
                ->assertDontSee('{company_')->assertDontSee('data-testid="legal-draft"', false);
            $this->get("/{$locale}/privacy")->assertOk()->assertSee($privacy)->assertSee('Voorbeeld Hosting')->assertDontSee('{hosting_provider}')->assertDontSee('{legal_date}');
        }
    }

    public function test_the_last_updated_date_is_shown_per_language(): void
    {
        $this->company();

        $this->get('/nl/terms')->assertSee('Laatst bijgewerkt op 1 november 2026');
        $this->get('/en/terms')->assertSee('Last updated on 1 November 2026');
    }

    public function test_while_company_details_are_missing_the_page_is_a_marked_draft_and_not_indexed(): void
    {
        config(['serverscope.company' => ['name' => null, 'kvk' => null, 'vat_id' => null, 'address' => null, 'email' => null, 'hosting_provider' => null]]);

        $this->get('/nl/terms')->assertOk()->assertSee('data-testid="legal-draft"', false)->assertSee('[nog in te vullen]')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->get('/en/privacy')->assertOk()->assertSee('[to be completed]');
    }

    public function test_the_footer_links_to_both_pages_and_the_sitemap_lists_them(): void
    {
        $this->company();
        config(['serverscope.noindex' => false]);

        $this->get('/nl')->assertSee('/nl/terms', false)->assertSee('/nl/privacy', false);
        $sitemap = $this->get('/sitemap.xml')->assertOk()->getContent();
        foreach (['/nl/terms', '/en/terms', '/nl/privacy', '/en/privacy'] as $path) {
            $this->assertStringContainsString($path, $sitemap);
        }
    }

    public function test_company_values_are_escaped(): void
    {
        $this->company();
        config(['serverscope.company.name' => '<script>alert(1)</script>']);

        $this->get('/nl/terms')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }
}
