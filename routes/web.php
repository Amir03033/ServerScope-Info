<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocFeedbackController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PricingController;
use App\Http\Controllers\StatusController;
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
    ->middleware('set.locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])
            ->name('home');

        Route::get('/features', [HomeController::class, 'features'])
            ->name('features');

        Route::get('/docs', [DocumentationController::class, 'index'])
            ->name('docs.index');

        Route::get('/docs/search.json', [DocumentationController::class, 'searchIndex'])
            ->name('docs.search');

        Route::get('/docs/{slug}', [DocumentationController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('docs.show');

        Route::post('/docs/{slug}/feedback', [DocFeedbackController::class, 'store'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->middleware('throttle:10,1')
            ->name('docs.feedback');

        Route::get('/pricing', [PricingController::class, 'index'])
            ->name('pricing');

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
    });

// Dynamic so staging (and any non-production copy) disallows everything.
Route::get('/robots.txt', function () {
    $body = config('serverscope.noindex')
        ? "User-agent: *\nDisallow: /\n"
        : "User-agent: *\nDisallow: /admin\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');
