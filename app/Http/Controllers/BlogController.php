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
            ->get(['slug', 'title', 'excerpt', 'published_at'])
            ->map(fn (Post $post): array => [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'date' => $post->published_at->toDateString(),
            ])
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

        return view('blog.article', [
            'locale' => $locale,
            'article' => [
                'slug' => $post->slug,
                'title' => $post->title,
                'excerpt' => $post->excerpt,
                'date' => $post->published_at->toDateString(),
                'html' => $post->renderedBody(),
            ],
        ]);
    }
}
