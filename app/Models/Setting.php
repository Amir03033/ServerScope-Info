<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value site settings managed in the admin panel. Reads are cached;
 * missing keys fall back to DEFAULTS.
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public const CACHE_KEY = 'settings.all';

    public const DEFAULTS = [
        // Sign-up: while registration in ServerScope is off, every plan
        // button points to the waitlist or contact page.
        'registration_open' => false,
        'signup_url' => null,          // e.g. https://serverscope.nl/register
        'waitlist_url' => null,        // empty = contact page of this site
        'billing_toggle' => true,      // show the monthly/yearly switch
        'yearly_note' => ['nl' => null, 'en' => null],
        // Live status badge (public ServerScope status page).
        'status_page_url' => 'https://serverscope.nl/status/serverscope',
        // Agent release key fingerprint, filled in by the owner.
        'release_fingerprint' => null,
        // Cookieless page counter (aggregated per path per day).
        'pageview_counter' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = rescue(fn () => Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all()), [], report: false);

        return array_key_exists($key, $all) ? $all[$key] : ($default ?? self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
