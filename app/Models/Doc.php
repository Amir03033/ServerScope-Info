<?php

namespace App\Models;

use App\Enums\FeatureStatus;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'locale', 'translation_key', 'doc_category_id', 'feature_id', 'slug', 'title', 'description', 'body',
    'icon', 'sort', 'status', 'published_at', 'seo_title', 'seo_description', 'noindex',
])]
class Doc extends Model
{
    use SoftDeletes;

    /**
     * Reason stored with the next revision (import, restore, ...). Reset
     * after every save.
     */
    public ?string $revisionReason = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'imported_at' => 'datetime',
            'noindex' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Doc $doc): void {
            $doc->translation_key = $doc->translation_key ?: $doc->slug;

            if (auth()->id() !== null) {
                $doc->updated_by = auth()->id();
                $doc->created_by ??= auth()->id();
            }
        });

        static::saved(function (Doc $doc): void {
            if ($doc->wasChanged('slug') || $doc->wasChanged('locale')) {
                Redirect::moved(
                    static::pathFor($doc->getOriginal('locale'), $doc->getOriginal('slug')),
                    static::pathFor($doc->locale, $doc->slug),
                );
            }

            if ($doc->wasRecentlyCreated || $doc->wasChanged(['title', 'description', 'body', 'slug', 'status', 'published_at', 'seo_title', 'seo_description'])) {
                $doc->revisions()->create([
                    'user_id' => auth()->id(),
                    'reason' => $doc->revisionReason ?? ($doc->wasRecentlyCreated ? 'Aangemaakt' : 'Bewerkt'),
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'body' => $doc->body,
                    'meta' => $doc->only(['slug', 'status', 'published_at', 'seo_title', 'seo_description', 'icon', 'sort']),
                ]);
            }

            $doc->revisionReason = null;
        });
    }

    public static function pathFor(string $locale, string $slug): string
    {
        return "/{$locale}/docs/{$slug}";
    }

    public function path(): string
    {
        return static::pathFor($this->locale, $this->slug);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocCategory::class, 'doc_category_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocRevision::class)->latest('id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(DocFeedback::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Published (and due), and not tied to a hidden feature.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', PublishStatus::Published->value)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('feature_id')->orWhereHas(
                'feature',
                fn (Builder $f) => $f->where('status', '!=', FeatureStatus::Hidden->value),
            ));
    }

    public function isVisible(): bool
    {
        return $this->status === PublishStatus::Published
            && ($this->published_at === null || $this->published_at->isPast())
            && ($this->feature === null || $this->feature->status->isVisible());
    }

    public function isComingSoon(): bool
    {
        return $this->feature?->isComingSoon() ?? false;
    }

    /**
     * True when someone changed the page after the import (any revision that
     * the importer did not write itself). The importer never overwrites such
     * a page without --force.
     */
    public function editedSinceImport(): bool
    {
        return $this->revisions()->where('reason', 'not like', 'Import%')->where('reason', '!=', 'Aangemaakt')->exists()
            || ($this->imported_body_sha256 !== null && hash('sha256', $this->body) !== $this->imported_body_sha256);
    }
}
