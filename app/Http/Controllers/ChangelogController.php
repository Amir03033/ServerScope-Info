<?php

namespace App\Http\Controllers;

use App\Services\Changelog;
use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function index(string $locale, Changelog $changelog): View
    {
        return view('changelog.index', ['locale' => $locale, 'entries' => $changelog->entries($locale)]);
    }
}
