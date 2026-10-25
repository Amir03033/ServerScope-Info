<?php

namespace App\Http\Controllers;

use App\Models\Release;
use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function index(string $locale): View
    {
        $entries = Release::query()
            ->where('locale', $locale)
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Release $release): array => [
                'version' => $release->version,
                'date' => $release->released_at->toDateString(),
                'items' => collect($release->items)
                    ->map(fn (array $item): array => [$item['type'] ?? 'new', $item['text'] ?? ''])
                    ->all(),
            ])
            ->all();

        // No releases in the database yet: the view falls back to lang/{locale}/changelog.php.
        return view('changelog.index', ['locale' => $locale, 'entries' => $entries ?: null]);
    }
}
