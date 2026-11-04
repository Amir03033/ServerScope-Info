<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DemoButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config(['serverscope.app_url' => 'https://app.example.test']);
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_the_button_is_hidden_while_the_demo_is_off(): void
    {
        foreach (['/nl', '/en', '/nl/pricing', '/en/pricing'] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('data-demo-button', false)
                ->assertDontSee('/demo"', false)
                ->assertDontSee('Voorbeeldgegevens, geen account nodig')
                ->assertDontSee('Sample data, no account needed');
        }
    }

    public function test_the_button_points_to_the_app_demo_in_both_languages(): void
    {
        Setting::put('demo_enabled', true);

        $this->get('/nl')->assertOk()
            ->assertSeeInOrder(['Bekijk de functies', 'Lees de documentatie', 'Probeer de demo'])
            ->assertSee('href="https://app.example.test/demo"', false)
            ->assertSee('rel="nofollow" data-demo-button', false)
            ->assertSee('Voorbeeldgegevens, geen account nodig');
        $this->get('/en')->assertOk()->assertSee('Try the demo')->assertSee('Sample data, no account needed');

        // Hero and the call to action at the bottom.
        $this->assertSame(2, substr_count($this->get('/en')->getContent(), 'data-demo-button='));

        $this->get('/nl/pricing')->assertOk()->assertSee('Probeer de demo')->assertSee('href="https://app.example.test/demo"', false);
        $this->get('/en/pricing')->assertOk()->assertSee('Try the demo');
    }

    public function test_an_own_demo_url_wins_and_only_web_addresses_are_used(): void
    {
        Setting::put('demo_enabled', true);
        Setting::put('demo_url', 'https://demo.example.test/start');
        $this->get('/en')->assertSee('href="https://demo.example.test/start"', false);

        Setting::put('demo_url', 'javascript:alert(1)');
        $this->get('/en')->assertDontSee('data-demo-button', false)->assertDontSee('javascript:alert', false);
    }

    public function test_administrators_switch_the_demo_on_in_site_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $this->get('/admin/site-settings')->assertOk()->assertSee('Demo tonen');

        Livewire::test(SiteSettings::class)
            ->fillForm(['demo_enabled' => true, 'demo_url' => 'not a url'])
            ->call('save')
            ->assertHasFormErrors(['demo_url']);

        Livewire::test(SiteSettings::class)
            ->fillForm(['demo_enabled' => true, 'demo_url' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Setting::get('demo_enabled'));
        $this->get('/nl')->assertSee('href="https://app.example.test/demo"', false);
    }
}
