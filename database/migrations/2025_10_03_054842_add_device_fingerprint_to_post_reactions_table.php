<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->string('device_fingerprint')->nullable()->after('author_token');
            $table->index(['device_fingerprint', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reactions', function (Blueprint $table) {
            $table->dropIndex(['device_fingerprint', 'post_id']);
            $table->dropColumn('device_fingerprint');
        });
    }
};
