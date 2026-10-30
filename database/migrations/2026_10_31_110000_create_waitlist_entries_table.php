<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Waiting list. Only what is needed: the e-mail address, language,
     * chosen plan, the consent text that was shown and when consent was
     * given. No IP address or other visitor data.
     */
    public function up(): void
    {
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('email', 254)->unique();
            $table->string('locale', 2);
            $table->string('plan', 40)->nullable();
            $table->text('consent_text');
            $table->timestamp('consented_at');
            // SHA-256 of the personal removal link token (the token itself is never stored).
            $table->char('removal_token_hash', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waitlist_entries');
    }
};
