<?php

namespace App\Http\Controllers;

use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly DocumentationService $documentation
    ) {}

    public function index(Request $request, string $locale): View
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        return view('docs.index', [
            'locale' => $locale,
            'articles' => $this->documentation->all($locale),
            'query' => $query,
            'results' => $query !== '' ? $this->documentation->search($locale, $query) : null,
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $article = $this->documentation->find($locale, $slug);

        if ($article === null) {
            throw new NotFoundHttpException;
        }

        return view('docs.article', [
            'locale' => $locale,
            'article' => $article,
            'articles' => $this->documentation->all($locale),
        ]);
    }
}
