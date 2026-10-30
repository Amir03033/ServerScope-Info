<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\LegalVersion;
use App\Services\Legal\LegalRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Public legal pages. Only published versions are shown; a document
 * without a published version does not exist (404). Drafts are only
 * visible to panel users through the preview.
 */
class LegalController extends Controller
{
    public function show(LegalRenderer $renderer, string $locale, string $legalSlug): View
    {
        $document = $this->document($locale, $legalSlug);
        $version = $document->currentVersion() ?? abort(404);

        return $this->page($renderer, $document, $version, ['upcoming' => $document->upcomingVersion()]);
    }

    public function versions(string $locale, string $legalSlug, string $versions): View
    {
        $document = $this->document($locale, $legalSlug);
        $current = $document->currentVersion() ?? abort(404);
        $published = $document->versions()->where('status', 'published')->get();

        return view('legal.versions', [
            'locale' => $locale,
            'document' => $document,
            'current' => $current,
            'versions' => $published,
            'alternateUrls' => $this->alternates($document, '/'.($locale === 'en' ? 'versions' : 'versies'), fn ($d) => '/'.($d->locale === 'en' ? 'versions' : 'versies')),
        ]);
    }

    public function version(LegalRenderer $renderer, string $locale, string $legalSlug, string $versionWord, string $version): View
    {
        $document = $this->document($locale, $legalSlug);
        $record = $document->versions()->where('status', 'published')->where('version', $version)->first() ?? abort(404);

        return $this->page($renderer, $document, $record, ['archived' => $record->isNot($document->currentVersion())]);
    }

    /**
     * Preview of any version (also drafts) for panel users.
     */
    public function preview(Request $request, LegalRenderer $renderer, LegalVersion $version): Response
    {
        abort_unless($request->user()?->isAdmin(), 403);
        app()->setLocale($version->document->locale);

        return response($this->page($renderer, $version->document, $version, ['preview' => true])->render())
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    private function document(string $locale, string $slug): LegalDocument
    {
        return LegalDocument::query()->where('locale', $locale)->where('slug', $slug)->firstOrFail();
    }

    private function page(LegalRenderer $renderer, LegalDocument $document, LegalVersion $version, array $extra = []): View
    {
        $rendered = $renderer->render($version);

        return view('legal.show', [
            'locale' => $document->locale,
            'document' => $document,
            'version' => $version,
            'html' => $rendered['html'],
            'toc' => $rendered['toc'],
            'changelog' => $rendered['changelog'],
            'hasVersions' => $document->versions()->where('status', 'published')->count() > 1,
            'alternateUrls' => $this->alternates($document),
            ...$extra,
        ]);
    }

    /**
     * NL/EN addresses of the same document (slugs differ per language).
     *
     * @return array<string, string>
     */
    private function alternates(LegalDocument $document, string $suffix = '', ?callable $suffixFor = null): array
    {
        $urls = [$document->locale => url($document->path().$suffix)];
        $translation = $document->translation();

        if ($translation?->currentVersion() !== null) {
            $urls[$translation->locale] = url($translation->path().($suffixFor ? $suffixFor($translation) : $suffix));
        }

        return $urls;
    }
}
