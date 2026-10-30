<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'name', 'tagline', 'price_monthly', 'price_yearly', 'currency', 'price_note', 'is_recommended', 'cta_label', 'cta_url', 'sort', 'is_visible'])]
class Plan extends Model
{
    use Translatable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => 'array',
            'tagline' => 'array',
            'price_note' => 'array',
            'cta_label' => 'array',
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_recommended' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(PlanRowValue::class);
    }
}
