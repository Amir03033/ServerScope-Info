<?php

namespace Tests\Feature;

use App\Services\DocumentationService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    private const SLUGS = [
        'getting-started', 'server-monitoring', 'domain-monitoring', 'alerts-incidents',
        'discord-notifications', 'status-pages', 'api-webhooks', 'security-account',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_every_tutorial_exists_in_both_languages_in_the_same_order(): void
    {
        $docs = app(DocumentationService::class);

        foreach (['nl', 'en'] as $locale) {
            $this->assertSame(self::SLUGS, array_column($docs->all($locale), 'slug'), $locale);

            foreach ($docs->all($locale) as $article) {
                $this->assertNotEmpty($article['description'], "{$locale}/{$article['slug']} has no description");
            }
        }
    }

    public function test_index_lists_tutorials_and_articles_render_with_navigation(): void
    {
        $this->get('/nl/docs')->assertOk()->assertSee('Aan de slag')->assertSee('Servermonitoring')->assertSee('role="search"', false);
        $this->get('/en/docs')->assertOk()->assertSee('Getting started')->assertSee('Discord notifications');

        $this->get('/en/docs/server-monitoring')
            ->assertOk()
            ->assertSee('aria-label="Breadcrumb"', false)
            ->assertSee('rel="prev"', false)
            ->assertSee('rel="next"', false)
            ->assertSee('Getting started')          // previous
            ->assertSee('Domain monitoring')        // next
            ->assertSee('On this page')
            ->assertSee('id="troubleshooting"', false)
            ->assertSee('data-copy-label="Copy"', false)
            ->assertSee('class="callout callout-warning"', false)
            ->assertSee('SERVERSCOPE_URL=https://serverscope.nl', false);

        $html = $this->get('/nl/docs/alerts-incidents')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, '<h1'), 'the Markdown title is not rendered twice');

        $this->get('/nl/docs/getting-started')->assertOk()->assertDontSee('rel="prev"', false)->assertSee('rel="next"', false);
        $this->get('/nl/docs/security-account')->assertOk()->assertSee('rel="prev"', false)->assertDontSee('rel="next"', false);
    }

    public function test_links_into_the_application_follow_the_configured_url(): void
    {
        config(['serverscope.app_url' => 'https://app.example.test']);

        $this->get('/nl/docs/domain-monitoring')->assertOk()
            ->assertSee('href="https://app.example.test/domains/create"', false)
            ->assertDontSee('{app_url}');
    }

    public function test_search_finds_matching_tutorials(): void
    {
        $this->get('/en/docs?q=webhook+token')->assertOk()->assertSee('Search results for')->assertSee('Discord notifications');
        $this->get('/nl/docs?q=xyzzy-bestaat-niet')->assertOk()->assertSee('Geen resultaten');

        $results = app(DocumentationService::class)->search('en', 'rotate token');
        $this->assertContains('server-monitoring', array_column($results, 'slug'));
    }

    public function test_unknown_or_malicious_slugs_are_404(): void
    {
        $this->get('/nl/docs/does-not-exist')->assertNotFound();
        $this->get('/nl/docs/..%2F..%2F.env')->assertNotFound();
        $this->get('/de/docs')->assertNotFound();
    }

    public function test_tutorials_only_link_to_routes_that_exist_in_the_application(): void
    {
        // Verified against routes/web.php of ServerScope Core.
        $known = ['', '/login', '/servers', '/servers/create', '/domains/create', '/alert-rules', '/alert-rules/create',
            '/alerts', '/incidents', '/profile', '/profile/api-tokens', '/webhooks', '/status-pages', '/users', '/audit-logs'];

        foreach (['nl', 'en'] as $locale) {
            foreach (File::files(base_path("content/docs/{$locale}")) as $file) {
                preg_match_all('~\]\(\{app_url\}([^)\s]*)\)~', File::get($file->getPathname()), $m);

                foreach ($m[1] as $path) {
                    $this->assertContains($path, $known, "{$locale}/{$file->getFilename()} links to {$path}");
                }
            }
        }
    }

    public function test_no_real_secrets_are_published(): void
    {
        foreach (File::allFiles(base_path('content/docs')) as $file) {
            $content = File::get($file->getPathname());

            $this->assertDoesNotMatchRegularExpression('~discord(app)?\.com/api/(v\d+/)?webhooks/\d+/[A-Za-z0-9_-]{20,}~', $content, $file->getFilename());
            $this->assertDoesNotMatchRegularExpression('~\b(ssk|whsec)_[A-Za-z0-9]{8,}~', $content, $file->getFilename());
        }
    }
}
