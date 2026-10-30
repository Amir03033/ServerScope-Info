<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Crawlers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CrawlersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['serverscope.noindex' => false]);
    }

    public function test_all_ai_crawlers_including_training_are_allowed_by_default(): void
    {
        $robots = $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

        foreach (['GPTBot', 'ClaudeBot', 'Google-Extended', 'CCBot', 'Applebot-Extended', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'Perplexity-User', 'Claude-SearchBot', 'Claude-User'] as $agent) {
            $this->assertStringContainsString("User-agent: {$agent}\nDisallow: /admin\n", $robots);
        }

        $this->assertStringNotContainsString("Disallow: /\n", $robots);
        $this->assertStringContainsString("Disallow: /nl/waitlist/remove/\n", $robots);
        $this->assertStringContainsString('Sitemap: '.url('/sitemap.xml'), $robots);
    }

    public function test_a_crawler_switched_off_in_the_admin_panel_is_disallowed(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        Livewire::test(SiteSettings::class)
            ->assertFormFieldExists('crawlers.GPTBot')
            ->fillForm(['crawlers.GPTBot' => false, 'crawlers.CCBot' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(Crawlers::allowed('GPTBot'));
        $this->assertTrue(Crawlers::allowed('ClaudeBot'));

        $robots = $this->get('/robots.txt')->getContent();
        $this->assertStringContainsString("User-agent: GPTBot\nDisallow: /\n", $robots);
        $this->assertStringContainsString("User-agent: CCBot\nDisallow: /\n", $robots);
        $this->assertStringContainsString("User-agent: ClaudeBot\nDisallow: /admin\n", $robots);
        $this->assertStringContainsString("User-agent: *\nDisallow: /admin\n", $robots, 'normal search engines are unaffected');
    }

    public function test_staging_blocks_everything_whatever_the_switches_say(): void
    {
        Setting::put('crawlers', ['GPTBot' => true]);
        config(['serverscope.noindex' => true]);

        $this->assertSame("# Not indexed (staging or test)\nUser-agent: *\nDisallow: /\n", $this->get('/robots.txt')->getContent());
    }
}
