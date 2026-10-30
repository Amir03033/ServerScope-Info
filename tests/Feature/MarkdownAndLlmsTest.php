<?php

namespace Tests\Feature;

use App\Enums\FeatureStatus;
use App\Models\Doc;
use App\Models\Feature;
use App\Services\DocumentationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PublishesLegalDocuments;
use Tests\TestCase;

class MarkdownAndLlmsTest extends TestCase
{
    use PublishesLegalDocuments;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        config(['serverscope.noindex' => false]);
    }

    public function test_each_doc_has_a_markdown_version_with_canonical_to_the_html_page(): void
    {
        $response = $this->get('/en/docs/getting-started.md')->assertOk()
            ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')
            ->assertHeader('Link', '<'.url('/en/docs/getting-started').'>; rel="canonical"');

        $this->assertStringStartsWith("# Getting started\n\n> ", $response->getContent());
        $this->assertStringContainsString('https://serverscope.nl', $response->getContent(), '{app_url} is filled in');
        $this->assertStringNotContainsString('{app_url}', $response->getContent());
        $this->assertStringEndsWith('Source: '.url('/en/docs/getting-started')."\n", $response->getContent());

        $this->get('/en/docs/getting-started')
            ->assertSee('<link rel="alternate" type="text/markdown" href="'.url('/en/docs/getting-started.md').'" title="Markdown">', false);
        $this->get('/nl/docs/getting-started.md')->assertOk()->assertSee('Bron: '.url('/nl/docs/getting-started'), false);
        $this->get('/en/docs/does-not-exist.md')->assertNotFound();
    }

    public function test_legal_pages_have_a_markdown_version_only_once_published(): void
    {
        $this->get('/nl/privacy.md')->assertNotFound();

        $this->fillCompany();
        $this->publish('privacy');

        $markdown = $this->get('/nl/privacy.md')->assertOk()
            ->assertHeader('Link', '<'.url('/nl/privacy').'>; rel="canonical"')->getContent();

        $this->assertStringStartsWith("# Privacyverklaring\n\nVersie 1.0, geldt vanaf ", $markdown);
        $this->assertStringContainsString('Waarde company.name', $markdown);
        $this->assertStringNotContainsString('<!--', $markdown, 'lawyer notes stay internal');
    }

    public function test_llms_txt_lists_only_public_docs_and_available_features(): void
    {
        [$available, $coming, $hidden] = Feature::query()->orderBy('sort')->take(3)->get()->all();
        $coming->update(['status' => FeatureStatus::ComingSoon]);
        $hidden->update(['status' => FeatureStatus::Hidden]);

        $llms = $this->get('/llms.txt')->assertOk()->assertHeader('Content-Type', 'text/markdown; charset=UTF-8')->getContent();

        $this->assertStringStartsWith("# ServerScope\n\n> ", $llms);
        $this->assertStringContainsString('- '.$available->name['en'].': ', $llms);
        $this->assertStringNotContainsString('- '.$coming->name['en'].': ', $llms);
        $this->assertStringNotContainsString('- '.$hidden->name['en'].': ', $llms);
        $this->assertStringContainsString('[Getting started]('.url('/en/docs/getting-started.md').')', $llms);

        // A doc about a feature that is not available yet is left out.
        $doc = Doc::query()->where('locale', 'en')->where('slug', 'getting-started')->sole();
        $doc->update(['feature_id' => $coming->id]);
        app(DocumentationService::class)->flush();
        $this->assertStringNotContainsString('getting-started.md', $this->get('/llms.txt')->getContent());

        // Drafts never appear.
        $draft = Doc::query()->where('locale', 'nl')->first();
        $draft->update(['status' => 'draft']);
        $this->assertStringNotContainsString("/nl/docs/{$draft->slug}.md", $this->get('/nl/llms.txt')->getContent());

        $this->get('/nl/llms.txt')->assertOk()->assertSee('## Beschikbare functionaliteit');
    }

    public function test_llms_full_contains_the_text_of_every_listed_page(): void
    {
        $this->fillCompany();
        $this->publish('privacy', 'en');

        $full = $this->get('/llms-full.txt')->assertOk()->getContent();
        $this->assertStringContainsString("# Getting started\n", $full);
        $this->assertStringContainsString("# Privacy statement\n", $full);
        $this->assertStringContainsString('[Privacy statement]('.url('/en/privacy.md').')', $full);
        $this->assertStringNotContainsString('{company.', $full);
        $this->get('/nl/llms-full.txt')->assertOk()->assertSee('# Aan de slag', false);
        $this->get('/robots.txt')->assertSee(url('/llms.txt'));
    }
}
