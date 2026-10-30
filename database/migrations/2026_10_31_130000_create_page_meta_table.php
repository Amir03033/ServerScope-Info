<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Title, meta description and noindex for the fixed pages (home,
     * features, pricing …), managed in the admin panel. Empty = the
     * built-in default text.
     */
    public function up(): void
    {
        Schema::create('page_meta', function (Blueprint $table) {
            $table->id();
            $table->string('page', 40);
            $table->string('locale', 2);
            $table->string('title', 200)->nullable();
            $table->string('description', 300)->nullable();
            $table->boolean('noindex')->default(false);
            $table->timestamps();

            $table->unique(['page', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_meta');
    }
};
