<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Counts successful page views per path per day. Nothing about the
 * visitor is stored or remembered: no IP address, no user agent, no
 * referrer and no cookie. Obvious bots and prefetches are skipped by
 * looking at the request headers only; those headers are not saved.
 */
class CountPageView
{
    private const BOTS = '/bot|crawl|spider|slurp|preview|monitor|lighthouse|headless|curl|wget|python|go-http/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->countable($request, $response)) {
            rescue(function () use ($request): void {
                $path = '/'.trim(mb_substr($request->path(), 0, 250), '/');
                $day = now()->toDateString();

                DB::table('page_views')->insertOrIgnore(['path' => $path, 'day' => $day, 'views' => 0]);
                DB::table('page_views')->where('path', $path)->where('day', $day)->increment('views');
            }, report: false);
        }

        return $response;
    }

    private function countable(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && ! $request->headers->has('Purpose') && ! $request->headers->has('Sec-Purpose')
            && ! preg_match(self::BOTS, (string) $request->userAgent())
            && (bool) Setting::get('pageview_counter');
    }
}
