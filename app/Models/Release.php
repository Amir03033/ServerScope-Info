<?php

namespace App\Models;

use App\Enums\FeatureStatus;
use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['locale', 'version', 'title', 'released_at', 'items', 'body', 'status', 'feature_id'])]
class Release extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['released_at' => 'date', 'items' => 'array', 'status' => PublishStatus::class];
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Published, and not tied to a hidden feature.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', PublishStatus::Published->value)
            ->where(fn (Builder $q) => $q->whereNull('feature_id')->orWhereHas(
                'feature',
                fn (Builder $f) => $f->where('status', '!=', FeatureStatus::Hidden->value),
            ));
    }
}
