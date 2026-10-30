<?php

namespace App\Support;

use App\Models\PageMeta;

/**
 * Title, description and robots for fixed pages: the value from the admin
 * panel when set, otherwise the built-in default text.
 */
class Seo
{
    /** Recommended lengths (characters), shown as an indicator in the panel. */
    public const TITLE = [30, 60];

    public const DESCRIPTION = [70, 160];

    /** @var array<string, ?PageMeta> */
    private static array $cache = [];

    public static function meta(string $page, string $locale): ?PageMeta
    {
        if (array_key_exists($page.$locale, self::$cache)) {
            return self::$cache[$page.$locale];
        }

        return self::$cache[$page.$locale] = rescue(fn () => PageMeta::query()->where('page', $page)->where('locale', $locale)->first(), null, report: false);
    }

    public static function title(string $page, string $locale): string
    {
        return filled(self::meta($page, $locale)?->title) ? self::meta($page, $locale)->title : trans(PageMeta::PAGES[$page][2], [], $locale);
    }

    public static function description(string $page, string $locale): string
    {
        return filled(self::meta($page, $locale)?->description) ? self::meta($page, $locale)->description : trans(PageMeta::PAGES[$page][3], [], $locale);
    }

    public static function noindex(string $page, string $locale): bool
    {
        return (bool) self::meta($page, $locale)?->noindex;
    }

    public static function flush(): void
    {
        self::$cache = [];
    }

    /**
     * "42 tekens · goed" style hint for the admin panel.
     *
     * @param  array{0: int, 1: int}  $range
     */
    public static function lengthHint(?string $value, array $range): string
    {
        $length = mb_strlen((string) $value);

        if ($length === 0) {
            return 'Leeg: de standaardtekst wordt gebruikt';
        }

        $verdict = $length < $range[0] ? 'kort' : ($length > $range[1] ? 'te lang, wordt afgekapt' : 'goed');

        return "{$length} tekens · {$verdict} (advies {$range[0]}–{$range[1]})";
    }

    /**
     * @param  array{0: int, 1: int}  $range
     */
    public static function lengthColor(?string $value, array $range): string
    {
        $length = mb_strlen((string) $value);

        return $length === 0 ? 'gray' : (($length >= $range[0] && $length <= $range[1]) ? 'success' : 'warning');
    }
}
