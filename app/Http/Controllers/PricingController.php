<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PricingController extends Controller
{
    public function index(string $locale): View
    {
        return view('pages.pricing', compact('locale'));
    }
}
