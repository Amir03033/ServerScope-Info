<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\DocumentationService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /** Pages that exist in both languages, below /{locale}. */
    private const PAGES = ['', 'features', 'docs', 'blog', 'changelog', 'status', 'security', 'contact'];

    public function sitemap(DocumentationService $docs): Response
    {
        $urls = [];

        foreach (self::PAGES as $page) {
            $urls[] = $this->pair($page, $page);
        }

        foreach ($docs->all('nl') as $article) {
            $urls[] = $this->pair('docs/'.$article['slug'], $docs->exists('en', $article['slug']) ? 'docs/'.$article['slug'] : null);
        }

        foreach ($docs->all('en') as $article) {
            if (! $docs->exists('nl', $article['slug'])) {
                $urls[] = ['en' => url('en/docs/'.$article['slug'])];
            }
        }

        // Blog posts are per language; pair them only when the slug matches.
        $posts = Post::query()->published()->get(['locale', 'slug', 'updated_at'])->groupBy('locale');

        foreach (['nl', 'en'] as $locale) {
            $other = $locale === 'nl' ? 'en' : 'nl';

            foreach ($posts->get($locale, collect()) as $post) {
                $twin = $posts->get($other, collect())->firstWhere('slug', $post->slug);

                if ($twin && $locale === 'en') {
                    continue; // listed with its Dutch twin
                }

                $urls[] = array_filter([
                    $locale => url("{$locale}/blog/{$post->slug}"),
                    $other => $twin ? url("{$other}/blog/{$post->slug}") : null,
                    'lastmod' => $post->updated_at?->toDateString(),
                ]);
            }
        }

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /livewire',
            '',
            'Sitemap: '.url('sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** @return array<string, string> */
    private function pair(string $nl, ?string $en): array
    {
        $path = fn (string $locale, string $page) => url($locale.($page === '' ? '' : '/'.$page));

        return array_filter(['nl' => $path('nl', $nl), 'en' => $en === null ? null : $path('en', $en)]);
    }
}
