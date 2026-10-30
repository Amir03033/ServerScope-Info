<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Markdown\MarkdownRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * "Wat is ServerScope", the glossary, the comparison and the landing pages.
 * Everyone, bots included, gets the same HTML.
 */
class PageController extends Controller
{
    public function show(MarkdownRenderer $renderer, string $locale, string $pageSlug): View
    {
        $page = Page::query()->visible()->with('feature')->where('locale', $locale)->where('slug', $pageSlug)->first() ?? abort(404);

        return $this->view($renderer, $page);
    }

    /**
     * Preview of any page (also drafts) for panel users.
     */
    public function preview(Request $request, MarkdownRenderer $renderer, Page $page): Response
    {
        abort_unless($request->user()?->canAccessPanel(filament()->getPanel('admin')), 403);
        app()->setLocale($page->locale);

        return response($this->view($renderer, $page, preview: true)->render())->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function view(MarkdownRenderer $renderer, Page $page, bool $preview = false): View
    {
        $rendered = $renderer->render($renderer->prepare(MarkdownRenderer::withSummary($page->body, $page->summary), $page->locale), $page->locale);
        $faq = collect($page->faq ?? [])->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))
            ->map(fn ($item) => [
                'question' => $item['question'],
                'answer' => $item['answer'],
                'html' => $renderer->render($renderer->prepare($item['answer'], $page->locale), $page->locale)['html'],
            ])->values()->all();

        $translation = $page->translation();
        $alternates = [$page->locale => url($page->path())];
        if ($translation && $translation->status->value === 'published') {
            $alternates[$translation->locale] = url($translation->path());
        }

        return view('pages.content', [
            'locale' => $page->locale,
            'page' => $page,
            'html' => $rendered['html'],
            'faq' => $faq,
            'preview' => $preview,
            'alternateUrls' => $alternates,
        ]);
    }
}
