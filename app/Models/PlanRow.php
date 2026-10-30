<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['group', 'label', 'hint', 'feature_id', 'type', 'sort'])]
class PlanRow extends Model
{
    use Translatable;

    public const TYPES = ['limit' => 'Limiet (getal of tekst)', 'boolean' => 'Ja/nee', 'text' => 'Tekst'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['group' => 'array', 'label' => 'array', 'hint' => 'array'];
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(PlanRowValue::class);
    }
}
