<?php

namespace App\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AboutCommand::add('ServerScope Info', fn () => [
            'Staging (Basic Auth)' => config('serverscope.staging.enabled') ? '<fg=yellow;options=bold>ON</>' : 'OFF',
            'Noindex' => config('serverscope.noindex') ? 'ON' : 'OFF',
        ]);

        // STAGING must never stay on in production unnoticed: besides the
        // banner on every page, log it (at most once an hour).
        if (config('serverscope.staging.enabled') && $this->app->isProduction()) {
            rescue(function (): void {
                if (Cache::add('staging-in-production-logged', true, 3600)) {
                    Log::critical('STAGING=true while APP_ENV=production: the site is password protected and noindex.');
                }
            }, report: false);
        }
    }
}
