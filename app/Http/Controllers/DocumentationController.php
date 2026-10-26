<?php

namespace App\Http\Controllers;

use App\Services\DocumentationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly DocumentationService $documentation
    ) {}

    public function index(Request $request, string $locale): View
    {
        $query = $this->query($request);

        return view('docs.index', [
            'locale' => $locale,
            'articles' => $this->documentation->all($locale),
            'groups' => $this->documentation->grouped($locale),
            'query' => $query,
            'results' => $query !== '' ? $this->documentation->search($locale, $query) : null,
            // Keep the search when switching language.
            'switchUrl' => url(($locale === 'en' ? 'nl' : 'en').'/docs').($query !== '' ? '?'.http_build_query(['q' => $query]) : ''),
        ]);
    }

    /**
     * JSON for the instant search dialog; the HTML search page is the fallback.
     */
    public function search(Request $request, string $locale): JsonResponse
    {
        $query = $this->query($request);
        $labels = trans('site.docs.categories', [], $locale);

        $results = array_map(fn (array $result): array => [
            'title' => $result['title'],
            'category' => $labels[$result['category']] ?? null,
            'snippet' => $result['snippet'],
            'url' => url($locale.'/docs/'.$result['slug']),
        ], array_slice($query !== '' ? $this->documentation->search($locale, $query) : [], 0, 8));

        return response()->json(['data' => $results])->header('Cache-Control', 'public, max-age=300');
    }

    public function show(string $locale, string $slug): View|RedirectResponse
    {
        if (isset(DocumentationService::REDIRECTS[$slug])) {
            return redirect()->to(url($locale.'/docs/'.DocumentationService::REDIRECTS[$slug]), 301);
        }

        $article = $this->documentation->find($locale, $slug);

        if ($article === null) {
            throw new NotFoundHttpException;
        }

        $other = $locale === 'en' ? 'nl' : 'en';
        $translated = $this->documentation->exists($other, $slug);

        return view('docs.article', [
            'locale' => $locale,
            'article' => $article,
            'articles' => $this->documentation->all($locale),
            'groups' => $this->documentation->grouped($locale),
            'switchUrl' => url($other.'/docs'.($translated ? '/'.$slug : '')),
            'translated' => $translated,
        ]);
    }

    private function query(Request $request): string
    {
        return mb_substr(trim((string) $request->query('q', '')), 0, 100);
    }
}
