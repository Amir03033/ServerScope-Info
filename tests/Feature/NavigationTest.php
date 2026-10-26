<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_navigation_contains_the_required_items_in_both_languages(): void
    {
        $this->get('/nl')->assertOk()
            ->assertSee('aria-label="ServerScope — Home"', false)
            ->assertSeeInOrder(['Functionaliteiten', 'Documentatie', 'API', 'Blog', 'Changelog', 'Contact', 'Inloggen', 'Aan de slag']);

        $this->get('/en/features')->assertOk()
            ->assertSee('aria-label="ServerScope — Home"', false)
            ->assertSeeInOrder(['Features', 'Documentation', 'API', 'Blog', 'Changelog', 'Contact', 'Log in', 'Get started']);
    }

    public function test_login_and_get_started_point_to_the_application(): void
    {
        // Log in opens the application; Get started explains how to get an
        // account (there is no public sign-up).
        $this->get('/en')->assertOk()
            ->assertSee('href="https://serverscope.nl/login"', false)
            ->assertSee('href="http://localhost/en/docs/getting-started"', false);

        config(['serverscope.app_url' => 'https://staging.example.test']);

        $this->get('/nl/docs')->assertOk()
            ->assertSee('href="https://staging.example.test/login"', false)
            ->assertDontSee('href="https://serverscope.nl/login"', false);
    }

    public function test_active_item_is_marked(): void
    {
        $this->get('/en/docs')->assertOk()->assertSee('aria-current="page"', false);

        // The API link points into the docs; only one of the two is current.
        $html = $this->get('/en/docs/api-reference')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~href="http://localhost/en/docs/api-reference" class="ss-nav-link"\s+aria-current="page"~', $html);
        $this->assertDoesNotMatchRegularExpression('~href="http://localhost/en/docs" class="ss-nav-link"\s+aria-current="page"~', $html);
    }

    public function test_mobile_menu_is_an_accessible_disclosure(): void
    {
        $this->get('/nl')->assertOk()
            ->assertSee('data-menu-toggle aria-expanded="false" aria-controls="mobile-menu"', false)
            ->assertSee('id="mobile-menu"', false);
    }

    public function test_reveal_animations_are_opt_in_and_respect_reduced_motion(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $js = file_get_contents(resource_path('js/app.js'));

        // Hidden state only exists on the JS-added class, never on the data attribute.
        $this->assertStringNotContainsString('[data-reveal] {', $css);
        $this->assertStringContainsString('.ss-reveal {', $css);
        $this->assertMatchesRegularExpression('/prefers-reduced-motion: reduce\)\s*\{\s*\.ss-reveal/', $css);
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $js);
        $this->assertStringContainsString('IntersectionObserver', $js);

        $this->get('/nl')->assertOk()->assertSee('data-reveal', false)->assertDontSee('ss-reveal', false);
    }
}
