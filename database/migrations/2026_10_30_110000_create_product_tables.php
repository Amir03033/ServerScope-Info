<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->json('name');
            $table->json('tagline')->nullable();
            // Placeholders: null = "price follows", filled in by the owner.
            $table->decimal('price_monthly', 10, 2)->nullable();
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('price_note')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->json('cta_label')->nullable();
            $table->string('cta_url', 500)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_rows', function (Blueprint $table) {
            $table->id();
            $table->json('group');
            $table->json('label');
            $table->json('hint')->nullable();
            $table->foreignId('feature_id')->nullable()->constrained()->nullOnDelete();
            // limit | boolean | text
            $table->string('type', 20)->default('limit');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_row_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_row_id')->constrained()->cascadeOnDelete();
            $table->string('value', 200)->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'plan_row_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Changelog: richer entries next to the existing item list. Existing
        // rows stay published and keep their items.
        Schema::table('releases', function (Blueprint $table) {
            $table->string('title', 200)->nullable()->after('version');
            $table->longText('body')->nullable()->after('items');
            $table->string('status', 20)->default('published')->after('body');
            $table->foreignId('feature_id')->nullable()->after('status')->constrained()->nullOnDelete();
            $table->string('source_key', 100)->nullable()->after('feature_id');
            $table->char('source_sha256', 64)->nullable()->after('source_key');
        });

        // Roles in the admin panel: admin | editor. Existing administrators
        // become "admin"; is_admin stays (mirrors the admin role).
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->nullable()->after('is_admin');
        });

        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('releases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('feature_id');
            $table->dropColumn(['title', 'body', 'status', 'source_key', 'source_sha256']);
        });

        Schema::dropIfExists('settings');
        Schema::dropIfExists('plan_row_values');
        Schema::dropIfExists('plan_rows');
        Schema::dropIfExists('plans');
    }
};
