<?php

namespace Tests\Feature;

use App\Support\OgImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ShareImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        File::deleteDirectory(OgImage::directory());
    }

    private function shareImage(string $path): string
    {
        $html = $this->get($path)->assertOk()->getContent();
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        preg_match('#<meta property="og:image" content="([^"]+)"#', $html, $m);

        return $m[1];
    }

    public function test_each_page_gets_its_own_image_in_the_house_style(): void
    {
        if (! OgImage::available()) {
            $this->markTestSkipped('GD with FreeType is not installed');
        }

        $docs = $this->shareImage('/en/docs/getting-started');
        $pricing = $this->shareImage('/en/pricing');

        $this->assertMatchesRegularExpression('#/og/[a-f0-9]{32}\.png$#', $docs);
        $this->assertNotSame($docs, $pricing);
        $this->assertSame($docs, $this->shareImage('/en/docs/getting-started'), 'drawn once, then reused');

        $response = $this->get(parse_url($docs, PHP_URL_PATH))->assertOk()->assertHeader('Content-Type', 'image/png');
        $size = getimagesizefromstring(file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->assertSame([1200, 630], [$size[0], $size[1]]);
    }

    public function test_only_images_drawn_for_a_page_are_served(): void
    {
        $this->get('/og/'.str_repeat('a', 32).'.png')->assertNotFound();
        $this->get('/og/../.env.png')->assertNotFound();
    }

    public function test_without_gd_every_page_uses_the_fixed_image(): void
    {
        config(['serverscope.og_images' => false]);

        $this->assertSame(url('/images/og-default.png'), $this->shareImage('/nl/features'));
        $this->assertFileExists(public_path('images/og-default.png'));
        $this->assertSame([1200, 630], array_slice(getimagesize(public_path('images/og-default.png')), 0, 2));
    }
}
