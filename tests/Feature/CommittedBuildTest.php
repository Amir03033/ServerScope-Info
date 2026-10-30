<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * public/build is committed so the server needs no Node/npm. This guards
 * that the manifest is present and every file it points to exists.
 * After changing CSS or JavaScript: npm run build, and commit public/build.
 */
class CommittedBuildTest extends TestCase
{
    public function test_committed_build_is_complete(): void
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath, 'Run npm run build and commit public/build.');

        $manifest = json_decode((string) file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

        foreach (['resources/css/app.css', 'resources/js/app.js', 'resources/fonts/inter-latin-wght.woff2'] as $entry) {
            $this->assertArrayHasKey($entry, $manifest);
            $this->assertFileExists(public_path('build/'.$manifest[$entry]['file']));
        }

        $this->assertFileDoesNotExist(public_path('hot'), 'public/hot (Vite dev server) must not be deployed.');
    }
}
