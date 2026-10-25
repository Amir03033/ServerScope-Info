<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->string('slug', 150);
            $table->string('title', 150);
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
