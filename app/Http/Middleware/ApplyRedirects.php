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
            $path = '/'.trim($request->path(), '/');
            // One indexed lookup; always current, also after bulk deletes.
            $redirect = rescue(fn () => Redirect::query()->where('from_path', $path)->first(['id', 'to_path', 'status_code']), null, report: false);

            if ($redirect !== null) {
                [$to, $status] = [$redirect->to_path, $redirect->status_code];
                rescue(fn () => $redirect->newQuery()->whereKey($redirect->id)->increment('hits'), report: false);

                $query = $request->getQueryString();

                return redirect()->to(($to === '/' || str_starts_with($to, 'http') ? $to : url($to)).($query ? '?'.$query : ''), $status);
            }
        }

        return $next($request);
    }
}
