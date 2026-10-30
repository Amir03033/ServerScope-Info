<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->json('name');
            $table->json('summary')->nullable();
            $table->string('icon', 40)->default('pulse');
            $table->string('category', 40)->nullable();
            // available | coming_soon | hidden
            $table->string('status', 20)->default('hidden')->index();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
