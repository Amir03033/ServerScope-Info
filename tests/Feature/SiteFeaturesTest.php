<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Models\Post;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SiteFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function contactPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'subject' => 'Vraag over de agent',
            'message' => 'Hoe vernieuw ik het agenttoken?',
        ], $overrides);
    }

    // Contact ------------------------------------------------------------

    public function test_contact_message_is_mailed_to_the_configured_recipient(): void
    {
        Mail::fake();
        config(['serverscope.contact_recipient' => 'team@example.com']);

        $this->get('/nl/contact')->assertOk()->assertSee('name="message"', false)->assertSee('name="website"', false);

        $this->post('/nl/contact', $this->contactPayload())
            ->assertRedirect('/nl/contact')
            ->assertSessionHas('success', 'Bedankt, je bericht is verstuurd.');

        Mail::assertSent(ContactMessage::class, fn (ContactMessage $mail) => $mail->hasTo('team@example.com')
            && $mail->hasReplyTo('ada@example.com')
            && $mail->data['subject'] === 'Vraag over de agent');
    }

    public function test_contact_form_is_hidden_and_never_claims_success_without_a_recipient(): void
    {
        Mail::fake();
        config(['serverscope.contact_recipient' => null]);

        $this->get('/en/contact')->assertOk()
            ->assertSee('The contact form is not available yet')
            ->assertDontSee('name="message"', false);

        $this->from('/en/contact')->post('/en/contact', $this->contactPayload())
            ->assertRedirect('/en/contact')
            ->assertSessionHas('failure')
            ->assertSessionMissing('success');

        Mail::assertNothingSent();
    }

    public function test_contact_reports_a_delivery_failure_and_keeps_the_input(): void
    {
        config(['serverscope.contact_recipient' => 'team@example.com']);
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

        $this->from('/en/contact')->post('/en/contact', $this->contactPayload())
            ->assertRedirect('/en/contact')
            ->assertSessionHas('failure', 'Your message could not be sent because of a technical problem. Please try again later.')
            ->assertSessionMissing('success')
            ->assertSessionHasInput('email', 'ada@example.com');
    }

    public function test_contact_validation_messages_are_dutch_on_the_dutch_site(): void
    {
        config(['serverscope.contact_recipient' => 'team@example.com']);

        $this->from('/nl/contact')->post('/nl/contact', $this->contactPayload(['email' => 'geen-adres', 'message' => '']))
            ->assertRedirect('/nl/contact')
            ->assertSessionHasErrors([
                'email' => 'Vul een geldig e-mailadres in.',
                'message' => 'Vul je bericht in.',
            ]);
    }

    public function test_contact_honeypot_sends_nothing(): void
    {
        Mail::fake();
        config(['serverscope.contact_recipient' => 'team@example.com']);

        $this->post('/en/contact', $this->contactPayload(['website' => 'http://spam.example']))->assertRedirect('/en/contact');

        Mail::assertNothingSent();
    }

    public function test_contact_subject_can_be_prefilled(): void
    {
        config(['serverscope.contact_recipient' => 'team@example.com']);

        $this->get('/en/contact?subject='.urlencode('Documentation: API reference'))
            ->assertOk()
            ->assertSee('value="Documentation: API reference"', false);
    }

    // Status -------------------------------------------------------------

    public function test_status_page_never_invents_a_status(): void
    {
        config(['serverscope.status_page' => null]);
        Http::fake();

        $this->get('/en/status')->assertOk()
            ->assertSee('No live status connected yet')
            ->assertDontSee('All components operational');

        Http::assertNothingSent();
    }

    public function test_status_page_shows_live_data_from_the_public_status_page(): void
    {
        config(['serverscope.status_page' => 'serverscope', 'serverscope.app_url' => 'https://app.example.test']);
        Http::fake(['https://app.example.test/status/serverscope.json' => Http::response([
            'name' => 'ServerScope',
            'overall' => 'degraded',
            'components' => [
                ['name' => 'Dashboard', 'description' => null, 'state' => 'operational', 'uptime' => [['date' => '2026-10-25', 'uptime' => 100], ['date' => '2026-10-26', 'uptime' => 98.5]]],
                ['name' => 'API', 'state' => 'degraded', 'uptime' => null],
                ['name' => '<script>alert(1)</script>', 'state' => 'not-a-state'],
            ],
            'incidents' => [['title' => 'Trage API', 'component' => 'API', 'severity' => 'warning', 'status' => 'investigating', 'started_at' => '2026-10-26T09:00:00+00:00', 'resolved_at' => null]],
            'generated_at' => '2026-10-26T10:00:00+00:00',
        ])]);

        $this->get('/nl/status')->assertOk()
            ->assertSee('Sommige componenten presteren minder goed')
            ->assertSee('Dashboard')
            ->assertSee('Trage API')
            ->assertSee('In onderzoek')
            ->assertSee('Onbekend') // unknown state is normalised
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('href="https://app.example.test/status/serverscope"', false);
    }

    public function test_status_page_says_so_when_the_source_is_unreachable(): void
    {
        config(['serverscope.status_page' => 'serverscope']);
        Http::fake(['*' => Http::response('nope', 500)]);

        $this->get('/en/status')->assertOk()
            ->assertSee('Status could not be loaded')
            ->assertDontSee('All components operational');
    }

    // SEO ----------------------------------------------------------------

    public function test_sitemap_lists_both_languages_with_alternates(): void
    {
        Post::create(['locale' => 'nl', 'slug' => 'alleen-nl', 'title' => 'Alleen NL', 'body' => 'x', 'published_at' => now()->subDay()]);
        Post::create(['locale' => 'nl', 'slug' => 'concept', 'title' => 'Concept', 'body' => 'x']);

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertStringContainsString('<loc>http://localhost/nl/docs/api-reference</loc>', $xml);
        $this->assertStringContainsString('<loc>http://localhost/en/docs/api-reference</loc>', $xml);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/security"', $xml);
        $this->assertStringContainsString('<loc>http://localhost/nl/blog/alleen-nl</loc>', $xml);
        $this->assertStringNotContainsString('concept', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));
    }

    public function test_robots_points_to_the_sitemap_and_keeps_the_admin_out(): void
    {
        $this->get('/robots.txt')->assertOk()
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: http://localhost/sitemap.xml');
    }

    public function test_pages_have_social_metadata_and_a_custom_404(): void
    {
        $this->get('/en/features')->assertOk()
            ->assertSee('property="og:image"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);

        $this->get('/en/does-not-exist')->assertNotFound()->assertSee('Page not found')->assertSee('noindex', false);
        $this->get('/nl/docs/bestaat-niet')->assertNotFound()->assertSee('Pagina niet gevonden');
    }

    // Language switching -------------------------------------------------

    public function test_language_switch_keeps_the_docs_search(): void
    {
        $this->get('/nl/docs?q=agent')->assertOk()
            ->assertSee('href="http://localhost/en/docs?q=agent"', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_blog_post_without_translation_switches_to_the_blog_index(): void
    {
        Post::create(['locale' => 'nl', 'slug' => 'nieuws', 'title' => 'Nieuws', 'body' => 'Tekst', 'published_at' => now()->subDay()]);

        $html = $this->get('/nl/blog/nieuws')->assertOk()->getContent();

        $this->assertStringContainsString('href="http://localhost/en/blog" hreflang="en"', $html);
        $this->assertStringNotContainsString('rel="alternate" hreflang="en"', $html);

        Post::create(['locale' => 'en', 'slug' => 'nieuws', 'title' => 'News', 'body' => 'Text', 'published_at' => now()->subDay()]);

        $this->get('/nl/blog/nieuws')->assertOk()
            ->assertSee('rel="alternate" hreflang="en" href="http://localhost/en/blog/nieuws"', false);
    }

    // Documentation ------------------------------------------------------

    public function test_old_api_page_redirects_to_the_api_reference(): void
    {
        $this->get('/en/docs/api-webhooks')->assertRedirect('http://localhost/en/docs/api-reference')->assertStatus(301);
    }

    public function test_search_endpoint_returns_json_results(): void
    {
        $this->getJson('/en/docs/search.json?q=signature')
            ->assertOk()
            ->assertJsonPath('data.0.url', 'http://localhost/en/docs/webhooks')
            ->assertJsonStructure(['data' => [['title', 'category', 'snippet', 'url']]]);

        $this->getJson('/en/docs/search.json?q=')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_docs_sidebar_is_grouped_and_code_blocks_are_enhanceable(): void
    {
        $this->get('/en/docs/api-reference')->assertOk()
            ->assertSee('Developers')
            ->assertSee('Account &amp; security', false)
            ->assertSee('data-code-blocks', false)
            ->assertSee('class="language-python"', false)
            ->assertSee('id="creating-a-token"', false)
            ->assertSee('"@type":"TechArticle"', false);
    }

    // Changelog ----------------------------------------------------------

    public function test_changelog_prefers_database_releases_and_shows_all_types(): void
    {
        $this->get('/en/changelog')->assertOk()->assertSee('v0.1.0'); // fallback file

        Release::create(['locale' => 'en', 'version' => '0.2.0', 'released_at' => '2026-10-27', 'items' => [
            ['type' => 'new', 'text' => 'Discord notifications'],
            ['type' => 'changed', 'text' => 'New docs layout'],
        ]]);

        $this->get('/en/changelog')->assertOk()
            ->assertSee('id="v0-2-0"', false)
            ->assertSee('Added')
            ->assertSee('Changed')
            ->assertDontSee('v0.1.0');

        $this->get('/en')->assertOk()->assertSee('v0.2.0');
    }
}
