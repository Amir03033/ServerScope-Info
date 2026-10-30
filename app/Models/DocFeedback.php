<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['doc_id', 'helpful', 'comment'])]
class DocFeedback extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'doc_feedback';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['helpful' => 'boolean'];
    }

    public function doc(): BelongsTo
    {
        return $this->belongsTo(Doc::class);
    }
}
