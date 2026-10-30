<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['doc_id', 'user_id', 'reason', 'is_draft', 'applied_at', 'title', 'description', 'body', 'meta'])]
class DocRevision extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['meta' => 'array', 'is_draft' => 'boolean', 'applied_at' => 'datetime'];
    }

    /**
     * A draft that has not been published yet.
     */
    public function isPendingDraft(): bool
    {
        return $this->is_draft && $this->applied_at === null;
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
