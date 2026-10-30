<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_categories', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->string('translation_key', 100);
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->text('description')->nullable();
            $table->string('icon', 40)->default('book');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['locale', 'slug']);
            $table->unique(['locale', 'translation_key']);
        });

        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->string('translation_key', 150);
            $table->foreignId('doc_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('feature_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug', 150);
            $table->string('title', 200);
            $table->text('description')->nullable();
            // Markdown, stored exactly as written (including the "# Title" line
            // of imported pages, which the renderer leaves out).
            $table->longText('body');
            $table->string('icon', 40)->default('book');
            $table->unsignedInteger('sort')->default(1000);
            // draft | published; published_at in the future = scheduled.
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title', 200)->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->boolean('noindex')->default(false);
            // Import bookkeeping (content/docs/{locale}/{slug}.md).
            $table->string('source_path')->nullable();
            $table->char('source_sha256', 64)->nullable();
            $table->char('imported_body_sha256', 64)->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'status', 'published_at']);
            $table->index('translation_key');
        });

        Schema::create('doc_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason', 200)->nullable();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->longText('body');
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['doc_id', 'id']);
        });

        // "Was this helpful?" No IP address, user agent or cookie is stored.
        Schema::create('doc_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_id')->constrained()->cascadeOnDelete();
            $table->boolean('helpful');
            $table->string('comment', 500)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['doc_id', 'helpful']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 300)->unique();
            $table->string('to_path', 300);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('doc_feedback');
        Schema::dropIfExists('doc_revisions');
        Schema::dropIfExists('docs');
        Schema::dropIfExists('doc_categories');
    }
};
