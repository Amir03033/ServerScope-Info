<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicViewsRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withViewErrors([]);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function pages(): array
    {
        $views = ['pages.home', 'pages.features', 'pages.status', 'pages.contact',
                  'blog.index', 'blog.article', 'docs.index', 'docs.article', 'changelog.index'];

        $cases = [];
        foreach (['nl', 'en'] as $locale) {
            foreach ($views as $view) {
                $cases["{$view} ({$locale})"] = [$view, $locale];
            }
        }

        return $cases;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_view_renders_with_metadata_and_without_missing_translation_keys(string $view, string $locale): void
    {
        app()->setLocale($locale);

        $html = view($view, ['locale' => $locale])->render();

        $this->assertStringContainsString('<html lang="' . $locale . '"', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('hreflang="x-default"', $html);
        $this->assertStringContainsString('hreflang="nl"', $html);
        $this->assertStringContainsString('hreflang="en"', $html);
        // A missing translation makes trans() return the key itself.
        $this->assertDoesNotMatchRegularExpression('/\b(site|changelog)\.[a-z_]+(\.[a-z_]+)*\b/', strip_tags($html));
    }

    public function test_blog_article_renders_title_date_and_body(): void
    {
        $html = view('blog.article', [
            'locale' => 'en',
            'article' => ['title' => 'Hello & welcome', 'date' => '2026-10-01', 'html' => '<p>Body text</p>', 'excerpt' => 'Short summary'],
        ])->render();

        $this->assertStringContainsString('Hello &amp; welcome', $html);
        $this->assertStringContainsString('datetime="2026-10-01"', $html);
        $this->assertStringContainsString('<p>Body text</p>', $html);
        $this->assertStringContainsString('content="Short summary"', $html);
    }

    public function test_titles_are_escaped_exactly_once(): void
    {
        $html = view('docs.article', ['locale' => 'nl', 'article' => ['title' => '<b>x</b> & y', 'slug' => 'x']])->render();

        $this->assertStringNotContainsString('<b>x</b>', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    public function test_changelog_lists_release_in_both_locales(): void
    {
        $this->assertStringContainsString('v0.1.0', view('changelog.index', ['locale' => 'en'])->render());
        $this->assertStringContainsString('24 september 2026', view('changelog.index', ['locale' => 'nl'])->render());
    }

    public function test_language_switch_points_to_other_locale(): void
    {
        $html = view('pages.home', ['locale' => 'nl'])->render();

        $this->assertStringContainsString('hreflang="en" lang="en"', $html);
    }
}
