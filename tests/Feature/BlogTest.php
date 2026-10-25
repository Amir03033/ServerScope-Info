<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_published_posts_in_the_right_locale_are_listed(): void
    {
        Post::create(['locale' => 'nl', 'title' => 'Zichtbaar', 'body' => 'x', 'published_at' => now()->subDay()]);
        Post::create(['locale' => 'nl', 'title' => 'Concept', 'body' => 'x']);
        Post::create(['locale' => 'en', 'title' => 'English one', 'body' => 'x', 'published_at' => now()->subDay()]);

        $this->withoutVite();
        $this->withViewErrors([]);

        $this->get('/nl/blog')->assertOk()->assertSee('Zichtbaar')->assertDontSee('Concept')->assertDontSee('English one');
    }

    public function test_article_html_is_sanitised_and_drafts_are_404(): void
    {
        Post::create(['locale' => 'nl', 'title' => 'Veilig', 'body' => "Hallo <script>alert(1)</script> **vet** [x](javascript:alert(1))", 'published_at' => now()->subDay()]);
        Post::create(['locale' => 'nl', 'title' => 'Concept', 'body' => 'x']);

        $this->withoutVite();
        $this->withViewErrors([]);

        $this->get('/nl/blog/veilig')->assertOk()->assertSee('<strong>vet</strong>', false)->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('javascript:', false);
        $this->get('/nl/blog/concept')->assertNotFound();
    }
}
