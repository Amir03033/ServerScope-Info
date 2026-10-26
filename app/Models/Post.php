<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['locale', 'slug', 'title', 'excerpt', 'body', 'published_at'])]
class Post extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if (blank($post->slug)) {
                $post->slug = static::uniqueSlug($post);
            }
        });
    }

    /**
     * Slug from the title, unique within the locale (-2, -3, ... on collision).
     */
    public static function uniqueSlug(Post $post): string
    {
        $base = Str::limit(Str::slug($post->title), 140, '') ?: 'artikel';
        $slug = $base;

        for ($i = 2; static::query()
            ->where('locale', $post->locale)
            ->where('slug', $slug)
            ->when($post->exists, fn (Builder $q) => $q->whereKeyNot($post->getKey()))
            ->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Markdown -> HTML. Raw HTML in the source is stripped and unsafe links
     * (javascript:, data:) are removed, so the result is safe to print unescaped.
     */
    public function renderedBody(): string
    {
        return Str::markdown($this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
