<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 301-redirects managed in the admin panel (and created automatically when
 * a documentation slug changes). Only GET/HEAD; the map is cached.
 */
class ApplyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            $map = rescue(fn () => Redirect::map(), [], report: false);
            $path = '/'.trim($request->path(), '/');

            if (isset($map[$path])) {
                [$to, $status, $id] = $map[$path];
                rescue(fn () => Redirect::query()->whereKey($id)->increment('hits'), report: false);

                $query = $request->getQueryString();

                return redirect()->to(($to === '/' || str_starts_with($to, 'http') ? $to : url($to)).($query ? '?'.$query : ''), $status);
            }
        }

        return $next($request);
    }
}
