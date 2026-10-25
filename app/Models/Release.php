<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['locale', 'version', 'released_at', 'items'])]
class Release extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['released_at' => 'date', 'items' => 'array'];
    }
}
