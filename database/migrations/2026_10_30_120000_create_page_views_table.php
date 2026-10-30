<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cookieless page counter: only a path, a day and a number. No IP
     * address, user agent, referrer, cookie or any other visitor data.
     */
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->date('day');
            $table->unsignedInteger('views')->default(0);

            $table->unique(['path', 'day']);
            $table->index('day');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
