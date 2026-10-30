<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Content pages ("Wat is ServerScope", begrippenlijst, vergelijking,
     * landingspagina's) with revisions, and a "In het kort" summary for docs.
     * Additive only: existing docs keep their content (summary stays empty).
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60);
            $table->string('locale', 2);
            $table->string('slug', 120);
            $table->string('title', 200);
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->json('faq')->nullable();
            $table->string('seo_title', 200)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('feature_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['key', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 200)->nullable();
            $table->string('title', 200);
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->json('faq')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('docs', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('docs', function (Blueprint $table) {
            $table->dropColumn('summary');
        });
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};
