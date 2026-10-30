<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plan_id', 'plan_row_id', 'value'])]
class PlanRowValue extends Model
{
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function row(): BelongsTo
    {
        return $this->belongsTo(PlanRow::class, 'plan_row_id');
    }
}
