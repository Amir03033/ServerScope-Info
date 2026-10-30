<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['email', 'locale', 'plan', 'consent_text', 'consented_at'])]
#[Hidden(['removal_token_hash'])]
class WaitlistEntry extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['consented_at' => 'datetime'];
    }

    /**
     * New personal removal token; only its hash is stored. Returns the
     * plain token once, for the removal link.
     */
    public function issueRemovalToken(): string
    {
        $token = Str::random(48);
        $this->removal_token_hash = hash('sha256', $token);

        return $token;
    }

    public function removalTokenMatches(string $token): bool
    {
        return hash_equals($this->removal_token_hash, hash('sha256', $token));
    }
}
