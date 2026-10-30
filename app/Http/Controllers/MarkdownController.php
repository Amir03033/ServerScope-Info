<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Services\MarkdownExport;
use Illuminate\Http\Response;

/**
 * Plain-text versions for AI tools: each doc and legal page as Markdown
 * (canonical points to the HTML page) and /llms.txt, /llms-full.txt.
 */
class MarkdownController extends Controller
{
    public function doc(MarkdownExport $export, string $locale, string $slug): Response
    {
        $markdown = $export->doc($locale, $slug);
        abort_if($markdown === null, 404);

        return $this->respond($markdown, url("{$locale}/docs/{$slug}"));
    }

    public function legal(MarkdownExport $export, string $locale, string $legalSlug): Response
    {
        $document = LegalDocument::query()->where('locale', $locale)->where('slug', $legalSlug)->first();
        $markdown = $document ? $export->legal($document) : null;
        abort_if($markdown === null, 404);

        return $this->respond($markdown, url($document->path()));
    }

    public function llms(MarkdownExport $export, ?string $locale = null): Response
    {
        return $this->respond($export->llms($locale ?? 'en'));
    }

    public function llmsFull(MarkdownExport $export, ?string $locale = null): Response
    {
        return $this->respond($export->llms($locale ?? 'en', full: true));
    }

    private function respond(string $markdown, ?string $canonical = null): Response
    {
        $headers = ['Content-Type' => 'text/markdown; charset=UTF-8', 'Cache-Control' => 'public, max-age=600'];

        if ($canonical !== null) {
            $headers['Link'] = "<{$canonical}>; rel=\"canonical\"";
        }

        if (config('serverscope.noindex')) {
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }

        return response($markdown, 200, $headers);
    }
}
