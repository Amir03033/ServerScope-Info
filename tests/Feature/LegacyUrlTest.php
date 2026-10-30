<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every public URL that existed before the revamp still answers 200 (or
 * the same redirect as before), both before and after the documentation
 * import.
 */
class LegacyUrlTest extends TestCase
{
    use RefreshDatabase;

    public const DOC_SLUGS = [
        'getting-started', 'server-monitoring', 'domain-monitoring', 'alerts-incidents',
        'discord-notifications', 'status-pages', 'api-webhooks', 'security-account',
    ];

    /**
     * @return array<int, string>
     */
    public static function urls(): array
    {
        $urls = [];

        foreach (['nl', 'en'] as $locale) {
            foreach (['', '/features', '/docs', '/blog', '/changelog', '/contact', '/status'] as $path) {
                $urls[] = "/{$locale}{$path}";
            }

            foreach (self::DOC_SLUGS as $slug) {
                $urls[] = "/{$locale}/docs/{$slug}";
            }
        }

        return $urls;
    }

    private function assertLegacyUrlsWork(): void
    {
        $this->get('/')->assertRedirect('/nl');
        $this->get('/up')->assertOk();
        $this->get('/admin/login')->assertOk();

        foreach (self::urls() as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_old_urls_work_before_the_import(): void
    {
        $this->withoutVite();
        $this->assertLegacyUrlsWork();
    }

    public function test_old_urls_work_after_the_import(): void
    {
        $this->withoutVite();
        $this->artisan('docs:import')->assertSuccessful();
        $this->assertLegacyUrlsWork();
    }
}
