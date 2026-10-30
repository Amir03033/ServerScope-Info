<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'locale', 'slug', 'title', 'seo_description', 'sort'])]
class LegalDocument extends Model
{
    /** key => [nl slug, en slug, sort] */
    public const DOCUMENTS = [
        'privacy' => ['privacy', 'privacy', 10],
        'cookies' => ['cookies', 'cookies', 20],
        'imprint' => ['colofon', 'imprint', 30],
        'terms' => ['voorwaarden', 'terms', 40],
        'dpa' => ['verwerkersovereenkomst', 'dpa', 50],
        'aup' => ['acceptabel-gebruik', 'acceptable-use', 60],
        'disclosure' => ['responsible-disclosure', 'responsible-disclosure', 70],
    ];

    public static function slugFor(string $key, string $locale): string
    {
        return self::DOCUMENTS[$key][$locale === 'en' ? 1 : 0];
    }

    public static function slugPattern(): string
    {
        return collect(self::DOCUMENTS)->flatMap(fn ($d) => [$d[0], $d[1]])->unique()->implode('|');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LegalVersion::class)->orderByDesc('effective_at')->orderByDesc('id');
    }

    /**
     * The version in force today: published, effective date today or earlier.
     */
    public function currentVersion(): ?LegalVersion
    {
        return $this->versions()->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('effective_at')->orWhereDate('effective_at', '<=', today()))
            ->first();
    }

    /**
     * A published version that takes effect later, if any.
     */
    public function upcomingVersion(): ?LegalVersion
    {
        return $this->versions()->where('status', 'published')->whereDate('effective_at', '>', today())->reorder()->orderBy('effective_at')->first();
    }

    public function path(): string
    {
        return "/{$this->locale}/{$this->slug}";
    }

    public function translation(): ?self
    {
        return static::query()->where('key', $this->key)->where('locale', '!=', $this->locale)->first();
    }
}
