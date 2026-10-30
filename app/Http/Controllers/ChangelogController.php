<?php

namespace App\Http\Controllers;

use App\Models\Release;
use App\Support\Markdown\MarkdownRenderer;
use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function index(MarkdownRenderer $markdown, string $locale): View
    {
        $entries = Release::query()
            ->visible()
            ->where('locale', $locale)
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Release $release): array => [
                'id' => 'release-'.$release->id,
                'version' => $release->version,
                'title' => $release->title,
                'date' => $release->released_at->toDateString(),
                'items' => collect($release->items ?? [])
                    ->map(fn (array $item): array => [$item['type'] ?? 'new', $item['text'] ?? ''])
                    ->all(),
                'html' => filled($release->body) ? $markdown->render($release->body, $locale, withoutTitle: false)['html'] : null,
            ])
            ->all();

        // No releases in the database yet: the view falls back to lang/{locale}/changelog.php.
        return view('changelog.index', ['locale' => $locale, 'entries' => $entries ?: null]);
    }
}
