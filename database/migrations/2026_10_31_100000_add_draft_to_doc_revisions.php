<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Draft revisions: proposed content that is not live until someone
     * publishes it in the admin panel. The page itself stays unchanged.
     */
    public function up(): void
    {
        Schema::table('doc_revisions', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->after('reason');
            $table->timestamp('applied_at')->nullable()->after('is_draft');
        });
    }

    public function down(): void
    {
        Schema::table('doc_revisions', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'applied_at']);
        });
    }
};
