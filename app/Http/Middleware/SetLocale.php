<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) $request->route('locale', 'nl');

        abort_unless(in_array($locale, ['nl', 'en'], true), 404);

        App::setLocale($locale);

        return $next($request);
    }
}
