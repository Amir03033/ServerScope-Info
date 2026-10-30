<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\LegalDocument;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Post;
use Illuminate\Http\Response;

/**
 * sitemap.xml with hreflang alternates. Only what is visible on the site:
 * published, due, not tied to a hidden feature and not marked noindex.
 * Empty (no URLs) where the site must not be indexed.
 */
class SitemapController extends Controller
{
    /** Fixed pages (keys of PageMeta::PAGES). */
    public const PAGES = ['home', 'features', 'docs', 'pricing', 'changelog', 'blog', 'contact', 'status', 'waitlist'];

    public function __invoke(): Response
    {
        $entries = [];

        if (! config('serverscope.noindex')) {
            $hidden = rescue(fn () => PageMeta::query()->where('noindex', true)->get(['page', 'locale']), collect(), report: false);

            foreach (self::PAGES as $page) {
                $path = PageMeta::PAGES[$page][1];
                $alternates = collect(['nl', 'en'])
                    ->reject(fn (string $locale) => $hidden->contains(fn (PageMeta $m) => $m->page === $page && $m->locale === $locale))
                    ->mapWithKeys(fn (string $locale) => [$locale => url($locale.$path)])->all();

                if ($alternates !== []) {
                    $entries[] = ['alternates' => $alternates, 'lastmod' => null];
                }
            }

            $docs = rescue(fn () => Doc::query()->visible()->where('noindex', false)->get(['locale', 'slug', 'translation_key', 'updated_at']), collect(), report: false);

            foreach ($docs->groupBy('translation_key') as $translations) {
                $alternates = $translations->mapWithKeys(fn (Doc $d) => [$d->locale => url($d->path())])->all();
                $entries[] = ['alternates' => $alternates, 'lastmod' => $translations->max('updated_at')];
            }

            $pages = rescue(fn () => Page::query()->visible()->get(['key', 'locale', 'slug', 'updated_at']), collect(), report: false);

            foreach ($pages->groupBy('key') as $translations) {
                $entries[] = [
                    'alternates' => $translations->mapWithKeys(fn (Page $p) => [$p->locale => url($p->path())])->all(),
                    'lastmod' => $translations->max('updated_at'),
                ];
            }

            $legal = rescue(fn () => LegalDocument::query()->get(), collect(), report: false);

            foreach ($legal->groupBy('key') as $translations) {
                $published = $translations->mapWithKeys(fn (LegalDocument $d) => [$d->locale => $d->currentVersion()])->filter();

                if ($published->isNotEmpty()) {
                    $entries[] = [
                        'alternates' => $published->keys()->mapWithKeys(fn (string $locale) => [$locale => url($translations->firstWhere('locale', $locale)->path())])->all(),
                        'lastmod' => $published->max(fn ($version) => $version->published_at),
                    ];
                }
            }

            $posts = rescue(fn () => Post::query()->published()->get(['locale', 'slug', 'updated_at']), collect(), report: false);

            foreach ($posts as $post) {
                $entries[] = ['alternates' => [$post->locale => url("{$post->locale}/blog/{$post->slug}")], 'lastmod' => $post->updated_at];
            }
        }

        return response()->view('sitemap', ['entries' => $entries])->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
