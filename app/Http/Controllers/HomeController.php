<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\Changelog;
use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(string $locale, DocumentationService $docs, Changelog $changelog): View
    {
        // The homepage must render even when the database is unavailable;
        // the changelog and blog teasers are then left out (and reported).
        return view('pages.home', [
            'locale' => $locale,
            'guides' => array_slice($docs->all($locale), 0, 6),
            'releases' => rescue(fn () => array_slice($changelog->entries($locale), 0, 2), []),
            'posts' => rescue(fn () => Post::query()->published()->where('locale', $locale)->latest('published_at')->limit(3)
                ->get(['slug', 'title', 'excerpt', 'body', 'published_at'])
                ->map(fn (Post $post): array => $post->summary())->all(), []),
        ]);
    }

    public function features(string $locale): View
    {
        return view('pages.features', compact('locale'));
    }

    public function security(string $locale): View
    {
        return view('pages.security', compact('locale'));
    }
}
