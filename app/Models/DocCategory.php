<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['locale', 'translation_key', 'name', 'slug', 'description', 'icon', 'sort'])]
class DocCategory extends Model
{
    public function docs(): HasMany
    {
        return $this->hasMany(Doc::class);
    }
}
