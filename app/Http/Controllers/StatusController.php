<?php

namespace App\Http\Controllers;

use App\Services\ServiceStatus;
use Illuminate\Contracts\View\View;

class StatusController extends Controller
{
    public function __invoke(string $locale, ServiceStatus $status): View
    {
        return view('pages.status', ['locale' => $locale, 'status' => $status->current()]);
    }
}
