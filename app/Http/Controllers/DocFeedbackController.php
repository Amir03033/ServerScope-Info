<?php

namespace App\Http\Controllers;

use App\Models\Doc;
use App\Models\DocFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "Was this helpful?". Stores the answer and an optional comment only: no
 * IP address, user agent or any other data about the visitor.
 */
class DocFeedbackController extends Controller
{
    public function store(Request $request, string $locale, string $slug): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'helpful' => ['required', 'boolean'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $doc = Doc::query()->visible()->where('locale', $locale)->where('slug', $slug)->firstOrFail();

        DocFeedback::query()->create([
            'doc_id' => $doc->id,
            'helpful' => (bool) $data['helpful'],
            'comment' => filled($data['comment'] ?? null) ? trim(strip_tags($data['comment'])) : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true], 201);
        }

        return redirect()->to(url("{$locale}/docs/{$slug}").'#feedback-title')->with('feedback-sent', $doc->id);
    }
}
