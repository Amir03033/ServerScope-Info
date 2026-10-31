<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Setting;
use App\Services\Site\SiteData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        config(['serverscope.app_url' => 'https://app.example.test']);
    }

    public function test_plan_keys_match_the_application(): void
    {
        $this->assertSame(['free', 'pro', 'premium'], Plan::query()->orderBy('sort')->pluck('key')->all());
    }

    public function test_open_registration_links_each_plan_to_register_with_its_key(): void
    {
        Setting::put('registration_open', true);
        $site = app(SiteData::class);

        foreach (['free', 'pro', 'premium'] as $key) {
            $plan = Plan::query()->where('key', $key)->sole();
            $this->assertSame("https://app.example.test/register?plan={$key}", $site->planUrl($plan, 'nl'));
        }

        $this->get('/nl/pricing')->assertSee('https://app.example.test/register?plan=premium', false);
    }

    public function test_signup_url_setting_gets_the_plan_appended(): void
    {
        Setting::put('registration_open', true);
        Setting::put('signup_url', 'https://serverscope.nl/register');
        $pro = Plan::query()->where('key', 'pro')->sole();

        $this->assertSame('https://serverscope.nl/register?plan=pro', app(SiteData::class)->planUrl($pro, 'en'));

        Setting::put('signup_url', 'https://serverscope.nl/register?ref=x');
        $this->assertSame('https://serverscope.nl/register?ref=x&plan=pro', app(SiteData::class)->planUrl($pro, 'en'));
    }

    public function test_explicit_plan_link_wins_and_closed_registration_uses_the_waiting_list(): void
    {
        $pro = Plan::query()->where('key', 'pro')->sole();
        $this->assertSame(url('en/waitlist').'?plan=pro', app(SiteData::class)->planUrl($pro, 'en'));

        Setting::put('registration_open', true);
        $pro->update(['cta_url' => 'https://example.test/custom']);
        $this->assertSame('https://example.test/custom', app(SiteData::class)->planUrl($pro->fresh(), 'en'));
    }
}
