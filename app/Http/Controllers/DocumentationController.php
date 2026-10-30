<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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

    /**
     * Admin preview of unsaved form data (stored in the session by the
     * "Voorbeeld" action). Only for panel users; never indexed.
     */
    public function preview(Request $request, DocumentationService $documentation, string $token): Response
    {
        abort_unless($request->user()?->canAccessPanel(filament()->getPanel('admin')), 403);

        $data = $request->session()->get("doc-preview.{$token}");
        abort_if(! is_array($data), 404);

        $locale = ($data['locale'] ?? 'nl') === 'en' ? 'en' : 'nl';
        app()->setLocale($locale);
        $rendered = $documentation->render((string) ($data['body'] ?? ''), $locale);
        $feature = filled($data['feature_id'] ?? null) ? Feature::query()->find($data['feature_id']) : null;

        return response()->view('docs.article', [
            'locale' => $locale,
            'preview' => true,
            'article' => [
                'slug' => (string) ($data['slug'] ?? ''),
                'title' => (string) ($data['title'] ?? ''),
                'description' => $data['description'] ?? null,
                'html' => $rendered['html'],
                'toc' => $rendered['toc'],
                'coming_soon' => $feature?->isComingSoon() ?? false,
                'previous' => null,
                'next' => null,
            ],
            'articles' => $documentation->all($locale),
        ])->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
