<?php

namespace App\Models\Concerns;

/**
 * Small helper for JSON columns holding {"nl": "...", "en": "..."}.
 * Falls back to Dutch, then to any filled value.
 */
trait Translatable
{
    public function translate(string $attribute, ?string $locale = null): string
    {
        $values = (array) ($this->getAttribute($attribute) ?? []);
        $locale ??= app()->getLocale();

        foreach ([$locale, 'nl', 'en'] as $code) {
            if (filled($values[$code] ?? null)) {
                return (string) $values[$code];
            }
        }

        return '';
    }
}
