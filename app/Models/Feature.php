<?php

namespace App\Models;

use App\Enums\FeatureStatus;
use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'summary', 'icon', 'category', 'status', 'sort'])]
class Feature extends Model
{
    use Translatable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'summary' => 'array',
            'status' => FeatureStatus::class,
        ];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', '!=', FeatureStatus::Hidden->value);
    }

    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class);
    }

    public function isComingSoon(): bool
    {
        return $this->status === FeatureStatus::ComingSoon;
    }
}
