<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        // Only schema-related changes (no data generation here)
        DB::statement('CREATE INDEX IF NOT EXISTS posts_created_at_index ON posts (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS posts_status_created_at_index ON posts (status, created_at)');
    }

    public function down(): void
    {
        // Revert schema indexes
        DB::statement('DROP INDEX IF EXISTS posts_status_created_at_index');
        DB::statement('DROP INDEX IF EXISTS posts_created_at_index');
    }
};