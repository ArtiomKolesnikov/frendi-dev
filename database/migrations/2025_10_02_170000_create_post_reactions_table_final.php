<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('post_reactions');

        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // like | dislike
            $table->string('author_token', 64)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('author_display_name')->nullable();
            $table->timestamps();

            $table->index(['post_id', 'type']);
            $table->unique(['post_id', 'user_id']);
            $table->unique(['post_id', 'admin_id']);
            $table->unique(['post_id', 'author_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
}; 