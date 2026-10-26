<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_dutch_homepage(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/nl');
    }

    public function test_dutch_homepage_returns_successful_response(): void
    {
        $response = $this->get('/nl');

        $response->assertOk();
        $response->assertSee('ServerScope');
    }

    public function test_english_homepage_returns_successful_response(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
        $response->assertSee('ServerScope');
    }
}
