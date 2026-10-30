<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per document and language (privacy, cookies, imprint,
        // terms, dpa, aup, disclosure). Slugs are fixed per language.
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30);
            $table->string('locale', 2);
            $table->string('slug', 60);
            $table->string('title', 150);
            $table->string('seo_description', 300)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['key', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        // Versions with an effective date. Published versions stay public
        // under "earlier versions".
        Schema::create('legal_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20);
            $table->date('effective_at')->nullable();
            $table->longText('body');
            $table->text('changelog')->nullable();
            // draft | published
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            // Until ticked, the admin panel shows "Concept: nog niet juridisch gecontroleerd".
            $table->boolean('legal_reviewed')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['legal_document_id', 'version']);
            $table->index(['legal_document_id', 'status', 'effective_at']);
        });

        Schema::create('legal_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 200)->nullable();
            $table->longText('body');
            $table->text('changelog')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_revisions');
        Schema::dropIfExists('legal_versions');
        Schema::dropIfExists('legal_documents');
    }
};
