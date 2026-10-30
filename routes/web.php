<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocFeedbackController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MarkdownController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\SecurityTxtController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\WaitlistController;
use App\Models\LegalDocument;
use App\Support\Crawlers;
use App\Support\OgImage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/nl');

// Live status for the badge, served by this site (cached server-side).
Route::get('/status.json', [StatusController::class, 'badge'])
    ->middleware('throttle:60,1')
    ->name('status.badge');

// Admin preview of unsaved documentation (session token, panel users only).
Route::get('/admin-preview/docs/{token}', [DocumentationController::class, 'preview'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('docs.preview');

Route::pattern('locale', 'nl|en');

Route::prefix('{locale}')
    ->middleware(['set.locale', 'count.view'])
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])
            ->name('home');

        Route::get('/features', [HomeController::class, 'features'])
            ->name('features');

        Route::get('/docs', [DocumentationController::class, 'index'])
            ->name('docs.index');

        Route::get('/docs/search.json', [DocumentationController::class, 'searchIndex'])
            ->name('docs.search');

        Route::get('/docs/{slug}.md', [MarkdownController::class, 'doc'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('docs.markdown');

        Route::get('/llms.txt', [MarkdownController::class, 'llms'])->name('llms.locale');
        Route::get('/llms-full.txt', [MarkdownController::class, 'llmsFull'])->name('llms-full.locale');

        Route::get('/docs/{slug}', [DocumentationController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('docs.show');

        Route::post('/docs/{slug}/feedback', [DocFeedbackController::class, 'store'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->middleware('throttle:10,1')
            ->name('docs.feedback');

        Route::get('/pricing', [PricingController::class, 'index'])
            ->name('pricing');

        Route::get('/waitlist', [WaitlistController::class, 'create'])
            ->name('waitlist');

        Route::post('/waitlist', [WaitlistController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('waitlist.store');

        Route::get('/waitlist/remove/{entry}/{token}', [WaitlistController::class, 'confirmRemoval'])
            ->whereNumber('entry')
            ->where('token', '[A-Za-z0-9]{48}')
            ->name('waitlist.remove');

        Route::post('/waitlist/remove/{entry}/{token}', [WaitlistController::class, 'destroy'])
            ->whereNumber('entry')
            ->where('token', '[A-Za-z0-9]{48}')
            ->middleware('throttle:10,1')
            ->name('waitlist.destroy');

        Route::get('/blog', [BlogController::class, 'index'])
            ->name('blog.index');

        Route::get('/blog/{slug}', [BlogController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('blog.show');

        Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');

        Route::get('/contact', [ContactController::class, 'create'])
            ->name('contact');

        Route::post('/contact', [ContactController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('contact.store');

        Route::get('/status', [StatusController::class, 'show'])
            ->name('status');

        // Legal pages; slugs differ per language (privacy, colofon/imprint …).
        Route::get('/{legalSlug}.md', [MarkdownController::class, 'legal'])
            ->where('legalSlug', LegalDocument::slugPattern())
            ->name('legal.markdown');

        Route::get('/{legalSlug}', [LegalController::class, 'show'])
            ->where('legalSlug', LegalDocument::slugPattern())
            ->name('legal.show');

        Route::get('/{legalSlug}/{versions}', [LegalController::class, 'versions'])
            ->where('legalSlug', LegalDocument::slugPattern())
            ->where('versions', 'versies|versions')
            ->name('legal.versions');

        Route::get('/{legalSlug}/{versionWord}/{version}', [LegalController::class, 'version'])
            ->where('legalSlug', LegalDocument::slugPattern())
            ->where('versionWord', 'versie|version')
            ->where('version', '[0-9A-Za-z.\-]+')
            ->name('legal.version');
    });

// Preview of a legal version (also drafts), administrators only.
Route::get('/admin-preview/legal/{version}', [LegalController::class, 'preview'])
    ->name('legal.preview');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Share images; only files drawn for a real page exist (see App\Support\OgImage).
Route::get('/og/{hash}.png', function (string $hash) {
    $path = OgImage::directory()."/{$hash}.png";
    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=604800']);
})->where('hash', '[a-f0-9]{32}')->name('og-image');

Route::get('/.well-known/security.txt', SecurityTxtController::class)->name('security-txt');

// Dynamic so staging (and any non-production copy) disallows everything.
// llms.txt (https://llmstxt.org): English at the root, Dutch under /nl.
Route::get('/llms.txt', [MarkdownController::class, 'llms'])->name('llms');
Route::get('/llms-full.txt', [MarkdownController::class, 'llmsFull'])->name('llms-full');

Route::get('/robots.txt', function () {
    return response(Crawlers::robots(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');
