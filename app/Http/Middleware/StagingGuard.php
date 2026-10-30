<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staging protection, switched on with STAGING=true in .env:
 *
 * - every request (except the /up health check) needs HTTP Basic Auth with
 *   STAGING_USER / STAGING_PASSWORD; without both set the site fails closed
 *   (503) instead of opening up;
 * - every response carries X-Robots-Tag: noindex, nofollow.
 *
 * Outside production the noindex header is always sent, so a staging copy
 * that forgot the flag is still not indexed.
 */
class StagingGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('serverscope.staging.enabled')) {
            $response = $next($request);

            if (config('serverscope.noindex')) {
                $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            }

            return $response;
        }

        if (! $request->is('up') && ! $this->authorised($request)) {
            $configured = filled(config('serverscope.staging.user')) && filled(config('serverscope.staging.password'));

            $response = $configured
                ? response('Staging: login vereist.', 401, ['WWW-Authenticate' => 'Basic realm="ServerScope staging", charset="UTF-8"'])
                : response('Staging is ingeschakeld maar STAGING_USER/STAGING_PASSWORD ontbreken.', 503);
        } else {
            $response = $next($request);
        }

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function authorised(Request $request): bool
    {
        $user = (string) config('serverscope.staging.user');
        $password = (string) config('serverscope.staging.password');

        if ($user === '' || $password === '') {
            return false;
        }

        // hash_equals on both parts, evaluated together (no early exit).
        $userOk = hash_equals($user, (string) $request->getUser());
        $passwordOk = hash_equals($password, (string) $request->getPassword());

        return $userOk && $passwordOk;
    }
}
