<?php

namespace App\Http\Controllers;

use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly DocumentationService $documentation
    ) {}

    public function index(string $locale): View
    {
        return view('docs.index', [
            'locale' => $locale,
            'articles' => $this->documentation->all($locale),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $article = $this->documentation->find($locale, $slug);

        if ($article === null) {
            throw new NotFoundHttpException();
        }

        return view('docs.article', [
            'locale' => $locale,
            'article' => $article,
        ]);
    }
}
