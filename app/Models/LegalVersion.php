<?php

namespace App\Models;

use App\Services\Legal\CompanyData;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

#[Fillable(['legal_document_id', 'version', 'effective_at', 'body', 'changelog', 'status', 'published_at', 'legal_reviewed'])]
class LegalVersion extends Model
{
    public ?string $revisionReason = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['effective_at' => 'date', 'published_at' => 'datetime', 'legal_reviewed' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (LegalVersion $version): void {
            if (auth()->id() !== null) {
                $version->updated_by = auth()->id();
                $version->created_by ??= auth()->id();
            }

            // Publishing needs every company detail the document uses, and an
            // effective date. This is enforced here, not only in the panel.
            if ($version->status === 'published') {
                $missing = app(CompanyData::class)->missingFor($version->document->key);

                if ($missing !== []) {
                    throw ValidationException::withMessages(['status' => 'Publiceren kan nog niet. Vul eerst in onder Instellingen → Bedrijfsgegevens: '.implode(', ', $missing).'.']);
                }

                if ($version->effective_at === null) {
                    throw ValidationException::withMessages(['effective_at' => 'Kies een ingangsdatum voordat je publiceert.']);
                }

                $version->published_at ??= now();
            }
        });

        static::saved(function (LegalVersion $version): void {
            if ($version->wasRecentlyCreated || $version->wasChanged(['body', 'changelog', 'status', 'effective_at', 'version', 'legal_reviewed'])) {
                $version->revisions()->create([
                    'user_id' => auth()->id(),
                    'reason' => $version->revisionReason ?? ($version->wasRecentlyCreated ? 'Aangemaakt' : 'Bewerkt'),
                    'body' => $version->body,
                    'changelog' => $version->changelog,
                    'meta' => $version->only(['version', 'status', 'legal_reviewed']) + ['effective_at' => $version->effective_at?->toDateString()],
                ]);
            }

            $version->revisionReason = null;
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(LegalRevision::class)->latest('id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
