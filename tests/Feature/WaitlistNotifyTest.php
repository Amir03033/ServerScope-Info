<?php

namespace Tests\Feature;

use App\Mail\WaitlistOpened;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WaitlistNotifyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('site:defaults')->assertSuccessful();
        config(['serverscope.app_url' => 'https://app.example.test']);
        $this->post('/en/waitlist', ['email' => 'a@example.com', 'plan' => 'pro', 'consent' => '1']);
        $this->post('/nl/waitlist', ['email' => 'b@example.com', 'consent' => '1']);
    }

    public function test_nothing_is_sent_while_registration_is_closed_or_on_a_dry_run(): void
    {
        Mail::fake();
        $this->artisan('waitlist:notify', ['--send' => true])->assertFailed();

        Setting::put('registration_open', true);
        $this->artisan('waitlist:notify')->assertSuccessful();
        Mail::assertNothingSent();
    }

    public function test_each_person_gets_one_mail_with_their_plan_link(): void
    {
        Mail::fake();
        Setting::put('registration_open', true);

        $this->artisan('waitlist:notify', ['--send' => true])->assertSuccessful();
        $this->artisan('waitlist:notify', ['--send' => true])->assertSuccessful(); // second run: nobody left

        Mail::assertSent(WaitlistOpened::class, 2);
        Mail::assertSent(WaitlistOpened::class, fn ($m) => $m->hasTo('a@example.com') && $m->registerUrl === 'https://app.example.test/register?plan=pro' && str_contains($m->removeUrl, '/en/waitlist/remove/'));
        Mail::assertSent(WaitlistOpened::class, fn ($m) => $m->hasTo('b@example.com') && $m->registerUrl === 'https://app.example.test/register');
        $this->assertSame(0, WaitlistEntry::query()->whereNull('notified_at')->count());
    }
}
