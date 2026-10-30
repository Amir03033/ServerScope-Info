<?php

namespace Tests\Feature;

use Tests\TestCase;

class StagingGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function staging(?string $user = 'review', ?string $password = 'Long-secret-42'): void
    {
        config([
            'serverscope.staging' => ['enabled' => true, 'user' => $user, 'password' => $password],
            'serverscope.noindex' => true,
        ]);
    }

    public function test_staging_requires_basic_auth_everywhere_and_is_noindex(): void
    {
        $this->staging();

        foreach (['/nl', '/en/docs', '/admin/login', '/robots.txt'] as $url) {
            $this->get($url)
                ->assertUnauthorized()
                ->assertHeader('WWW-Authenticate')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }

        $this->withBasicAuth('review', 'wrong')->get('/nl')->assertUnauthorized();

        $this->withBasicAuth('review', 'Long-secret-42')->get('/nl')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee('STAGING');

        $this->withBasicAuth('review', 'Long-secret-42')->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_health_check_stays_reachable_for_monitoring(): void
    {
        $this->staging();

        $this->get('/up')->assertOk();
    }

    public function test_staging_without_credentials_fails_closed(): void
    {
        $this->staging(null, null);

        $this->get('/nl')->assertStatus(503);
        $this->withBasicAuth('', '')->get('/nl')->assertStatus(503);
    }

    public function test_production_without_staging_is_indexable_and_shows_no_banner(): void
    {
        config(['serverscope.staging' => ['enabled' => false, 'user' => null, 'password' => null], 'serverscope.noindex' => false]);

        $this->get('/nl')->assertOk()->assertHeaderMissing('X-Robots-Tag')->assertDontSee('STAGING')->assertDontSee('name="robots"', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertDontSee("Disallow: /\n", false);
    }

    public function test_staging_in_production_is_impossible_to_miss(): void
    {
        $this->staging();
        $this->app->detectEnvironment(fn () => 'production');

        $this->withBasicAuth('review', 'Long-secret-42')->get('/nl')->assertOk()->assertSee('STAGING staat aan terwijl APP_ENV=production');
    }
}
