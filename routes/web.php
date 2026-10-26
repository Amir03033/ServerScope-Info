<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/nl');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::pattern('locale', 'nl|en');

Route::prefix('{locale}')
    ->middleware('set.locale')
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])
            ->name('home');

        Route::get('/features', [HomeController::class, 'features'])
            ->name('features');

        Route::get('/security', [HomeController::class, 'security'])
            ->name('security');

        Route::get('/docs', [DocumentationController::class, 'index'])
            ->name('docs.index');

        Route::get('/docs/search.json', [DocumentationController::class, 'search'])
            ->middleware('throttle:60,1')
            ->name('docs.search');

        Route::get('/docs/{slug}', [DocumentationController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('docs.show');

        Route::get('/blog', [BlogController::class, 'index'])
            ->name('blog.index');

        Route::get('/blog/{slug}', [BlogController::class, 'show'])
            ->where('slug', '[a-zA-Z0-9_-]+')
            ->name('blog.show');

        Route::get('/changelog', [ChangelogController::class, 'index'])
            ->name('changelog');

        Route::get('/contact', [ContactController::class, 'create'])
            ->name('contact');

        Route::post('/contact', [ContactController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('contact.store');

        Route::get('/status', StatusController::class)
            ->name('status');
    });
