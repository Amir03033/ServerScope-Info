<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_path', 'to_path', 'status_code'])]
class Redirect extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect): void {
            $redirect->from_path = static::normalise($redirect->from_path);
            $redirect->to_path = str_starts_with($redirect->to_path, 'http') ? $redirect->to_path : static::normalise($redirect->to_path);
        });
    }

    public static function normalise(string $path): string
    {
        return '/'.trim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
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
    }
}
