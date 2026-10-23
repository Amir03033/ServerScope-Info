<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(string $locale): View
    {
        return view('blog.index', [
            'locale' => $locale,
            'articles' => [],
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        abort(404);
    }
}
