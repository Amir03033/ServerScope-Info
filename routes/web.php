<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangelogController;

Route::redirect('/', '/nl');

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

        Route::get('/docs/{slug}', [DocumentationController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('docs.show');

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

        Route::view('/status', 'pages.status')
            ->name('status');
    });

// Dynamic so staging (and any non-production copy) disallows everything.
Route::get('/robots.txt', function () {
    $body = config('serverscope.noindex')
        ? "User-agent: *\nDisallow: /\n"
        : "User-agent: *\nDisallow: /admin\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');
