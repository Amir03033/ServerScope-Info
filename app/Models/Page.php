<?php

namespace App\Models;

use App\Enums\FeatureStatus;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Content pages managed in the admin panel. The addresses are fixed per key
 * (see PAGES), so links and routes stay stable.
 */
#[Fillable(['key', 'locale', 'slug', 'title', 'summary', 'body', 'faq', 'seo_title', 'seo_description', 'status', 'feature_id', 'sort'])]
class Page extends Model
{
    /** key => [nl slug, en slug, feature key, kind] */
    public const PAGES = [
        'what-is' => ['wat-is-serverscope', 'what-is-serverscope', null, 'about'],
        'glossary' => ['begrippenlijst', 'glossary', null, 'about'],
        'compare' => ['vergelijken', 'compare', null, 'about'],
        'vps' => ['vps-monitoren', 'vps-monitoring', 'server-monitoring', 'landing'],
        'ssl' => ['ssl-certificaat-monitoren', 'ssl-certificate-monitoring', 'ssl-monitoring', 'landing'],
        'uptime' => ['uptime-monitoring', 'uptime-monitoring', 'domain-monitoring', 'landing'],
        'dns' => ['dns-wijzigingen-monitoren', 'dns-change-monitoring', 'dns-monitoring', 'landing'],
        'domain-expiry' => ['domeinverloop-monitoren', 'domain-expiry-monitoring', 'domain-expiry', 'landing'],
        'status-page' => ['statuspagina', 'status-page', 'status-pages', 'landing'],
    ];

    /** Revision reason for the next save (set by the admin panel or importer). */
    public ?string $revisionReason = null;

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            $page->updated_by = auth()->id() ?? $page->updated_by;
            $page->created_by ??= auth()->id();
        });

        static::saved(function (Page $page) {
            if ($page->wasRecentlyCreated || $page->wasChanged(['title', 'summary', 'body', 'faq'])) {
                $page->revisions()->create([
                    'user_id' => auth()->id(),
                    'reason' => $page->revisionReason ?? ($page->wasRecentlyCreated ? 'Aangemaakt' : 'Bewerkt'),
                    'title' => $page->title,
                    'summary' => $page->summary,
                    'body' => $page->body,
                    'faq' => $page->faq,
                ]);
            }
            $page->revisionReason = null;
        });
    }

    protected function casts(): array
    {
        return ['faq' => 'array', 'status' => PublishStatus::class];
    }

    public static function slugFor(string $key, string $locale): string
    {
        return self::PAGES[$key][$locale === 'en' ? 1 : 0];
    }

    public static function slugPattern(): string
    {
        return collect(self::PAGES)->flatMap(fn ($p) => [$p[0], $p[1]])->unique()->implode('|');
    }

    /**
     * Published and not tied to a hidden feature.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published->value)
            ->where(fn (Builder $q) => $q->whereNull('feature_id')->orWhereHas(
                'feature',
                fn (Builder $f) => $f->where('status', '!=', FeatureStatus::Hidden->value),
            ));
    }

    public function isComingSoon(): bool
    {
        return $this->feature?->status === FeatureStatus::ComingSoon;
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
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
