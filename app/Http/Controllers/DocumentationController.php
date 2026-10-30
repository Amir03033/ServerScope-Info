<?php

namespace App\Http\Controllers;

use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    // The service is resolved per request (not in the constructor): route
    // controllers are cached, the documentation is not.
    public function index(Request $request, DocumentationService $documentation, string $locale): View
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        return view('docs.index', [
            'locale' => $locale,
            'articles' => $documentation->all($locale),
            'query' => $query,
            'results' => $query !== '' ? $documentation->search($locale, $query) : null,
        ]);
    }

    public function show(DocumentationService $documentation, string $locale, string $slug): View
    {
        $article = $documentation->find($locale, $slug);

        if ($article === null) {
            throw new NotFoundHttpException;
        }

        return view('docs.article', [
            'locale' => $locale,
            'article' => $article,
            'articles' => $documentation->all($locale),
        ]);
    }
}
