<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->string('version', 20);
            $table->date('released_at');
            $table->json('items');
            $table->timestamps();

            $table->index(['locale', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
