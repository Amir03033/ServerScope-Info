<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(string $locale): View
    {
        $articles = Post::query()
            ->published()
            ->where('locale', $locale)
            ->latest('published_at')
            ->get(['slug', 'title', 'excerpt', 'body', 'published_at'])
            ->map(fn (Post $post): array => $post->summary())
            ->all();

        return view('blog.index', ['locale' => $locale, 'articles' => $articles]);
    }

    public function show(string $locale, string $slug): View
    {
        $post = Post::query()
            ->published()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Post::query()
            ->published()
            ->where('locale', $locale)
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get(['slug', 'title', 'excerpt', 'body', 'published_at'])
            ->map(fn (Post $item): array => $item->summary())
            ->all();

        // Posts are written per language; link the other language's post
        // only when one with the same slug exists, otherwise its blog index.
        $other = $locale === 'en' ? 'nl' : 'en';
        $translated = Post::query()->published()->where('locale', $other)->where('slug', $slug)->exists();

        return view('blog.article', [
            'locale' => $locale,
            'article' => $post->summary() + [
                'html' => $post->renderedBody(),
                'updated' => $post->updated_at?->toIso8601String(),
                'published' => $post->published_at->toIso8601String(),
            ],
            'related' => $related,
            'switchUrl' => url($other.'/blog'.($translated ? '/'.$slug : '')),
            'translated' => $translated,
        ]);
    }
}
