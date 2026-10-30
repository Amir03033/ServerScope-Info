<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['from_path', 'to_path', 'status_code'])]
class Redirect extends Model
{
    public const CACHE_KEY = 'redirects.map';

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            $redirect->from_path = static::normalise($redirect->from_path);
            $redirect->to_path = str_starts_with($redirect->to_path, 'http') ? $redirect->to_path : static::normalise($redirect->to_path);
        });

        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function normalise(string $path): string
    {
        return '/'.trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
    }

    /**
     * @return array<string, array{0: string, 1: int, 2: int}> from => [to, status, id]
     */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
            ->get(['id', 'from_path', 'to_path', 'status_code'])
            ->mapWithKeys(fn (Redirect $r) => [$r->from_path => [$r->to_path, $r->status_code, $r->id]])
            ->all());
    }

    /**
     * Record a moved page, and point older redirects to the new place
     * (no chains, no loops).
     */
    public static function moved(string $from, string $to): void
    {
        $from = static::normalise($from);
        $to = static::normalise($to);

        if ($from === $to) {
            return;
        }

        static::query()->where('from_path', $to)->get()->each->delete();
        static::query()->where('to_path', $from)->get()->each(fn (Redirect $r) => $r->update(['to_path' => $to]));
        static::query()->updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301]);
        Cache::forget(self::CACHE_KEY);
    }
}
