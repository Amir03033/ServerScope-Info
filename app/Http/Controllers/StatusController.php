<?php

namespace App\Http\Controllers;

use App\Services\Site\StatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class StatusController extends Controller
{
    public function show(StatusService $status, string $locale): View
    {
        return view('pages.status', ['locale' => $locale, 'status' => $status->current(), 'pageUrl' => $status->pageUrl()]);
    }

    /**
     * Badge data for the browser, served by this site (the browser never
     * contacts ServerScope itself).
     */
    public function badge(StatusService $status): JsonResponse
    {
        return response()->json(['state' => $status->current()['state']])
            ->header('Cache-Control', 'public, max-age=30');
    }
}
