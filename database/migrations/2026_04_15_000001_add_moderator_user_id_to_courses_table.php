<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'moderator_user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('moderator_user_id')
                    ->nullable()
                    ->after('faculty_user_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'moderator_user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('moderator_user_id');
            });
        }
    }
};
