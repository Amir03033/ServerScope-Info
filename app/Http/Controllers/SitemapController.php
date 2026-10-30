<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\Post;
use Illuminate\Http\Response;

/**
 * sitemap.xml with hreflang alternates. Only what is visible on the site:
 * published, due, not tied to a hidden feature and not marked noindex.
 * Empty (no URLs) where the site must not be indexed.
 */
class SitemapController extends Controller
{
    public const PAGES = ['', '/features', '/docs', '/pricing', '/changelog', '/blog', '/contact', '/status'];

    public function __invoke(): Response
    {
        $entries = [];

        if (! config('serverscope.noindex')) {
            foreach (self::PAGES as $path) {
                $entries[] = ['alternates' => ['nl' => url('nl'.$path), 'en' => url('en'.$path)], 'lastmod' => null];
            }

            $docs = rescue(fn () => Doc::query()->visible()->where('noindex', false)->get(['locale', 'slug', 'translation_key', 'updated_at']), collect(), report: false);

            foreach ($docs->groupBy('translation_key') as $translations) {
                $alternates = $translations->mapWithKeys(fn (Doc $d) => [$d->locale => url($d->path())])->all();
                $entries[] = ['alternates' => $alternates, 'lastmod' => $translations->max('updated_at')];
            }

            $posts = rescue(fn () => Post::query()->published()->get(['locale', 'slug', 'updated_at']), collect(), report: false);

            foreach ($posts as $post) {
                $entries[] = ['alternates' => [$post->locale => url("{$post->locale}/blog/{$post->slug}")], 'lastmod' => $post->updated_at];
            }
        }

        return response()->view('sitemap', ['entries' => $entries])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
