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
        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'faculty_user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('faculty_user_id')
                    ->nullable()
                    ->after('course_code')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'faculty_user_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('faculty_user_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('department_id');
            });
        }
    }
};
