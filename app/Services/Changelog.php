<?php

namespace App\Services;

use App\Models\Release;

/**
 * Release history for one locale, newest first.
 *
 * Source of truth: the releases table (managed in the admin panel). Only
 * while that table has no releases for a locale, lang/{locale}/changelog.php
 * is used, so a fresh installation still shows the first release.
 */
class Changelog
{
    public const TYPES = ['new', 'improved', 'changed', 'fixed'];

    /**
     * @return array<int, array{version: string, anchor: string, date: string, items: array<int, array{0: string, 1: string}>}>
     */
    public function entries(string $locale): array
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

        if ($entries === []) {
            $fallback = trans('changelog.entries', [], $locale);
            $entries = is_array($fallback) ? $fallback : [];
        }

        return array_map(fn (array $entry): array => $entry + ['anchor' => 'v'.str_replace('.', '-', (string) $entry['version'])], $entries);
    }
}
