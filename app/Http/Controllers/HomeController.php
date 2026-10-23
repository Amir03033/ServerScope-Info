<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(string $locale): View
    {
        return view('pages.home', compact('locale'));
    }

    public function features(string $locale): View
    {
        return view('pages.features', compact('locale'));
    }
}
