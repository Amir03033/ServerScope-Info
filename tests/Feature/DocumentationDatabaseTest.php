<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Runs every documentation test again after the import, so the site
 * behaves the same from the database as it did from the Markdown files.
 */
class DocumentationDatabaseTest extends DocumentationTest
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('docs:import')->assertSuccessful();
    }
}
