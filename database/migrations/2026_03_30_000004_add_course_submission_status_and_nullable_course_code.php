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
        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (! Schema::hasColumn('departments', 'courses_submitted_to_cdc_at')) {
                    $table->timestamp('courses_submitted_to_cdc_at')->nullable()->after('assigned_user_id');
                }

                if (! Schema::hasColumn('departments', 'courses_submitted_by_user_id')) {
                    $table->foreignId('courses_submitted_by_user_id')
                        ->nullable()
                        ->after('courses_submitted_to_cdc_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('departments', 'course_codes_assigned_at')) {
                    $table->timestamp('course_codes_assigned_at')->nullable()->after('courses_submitted_by_user_id');
                }

                if (! Schema::hasColumn('departments', 'course_codes_assigned_by_user_id')) {
                    $table->foreignId('course_codes_assigned_by_user_id')
                        ->nullable()
                        ->after('course_codes_assigned_at')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'course_code')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('course_code')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'course_code')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('course_code')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('departments')) {
            Schema::table('departments', function (Blueprint $table) {
                if (Schema::hasColumn('departments', 'course_codes_assigned_by_user_id')) {
                    $table->dropConstrainedForeignId('course_codes_assigned_by_user_id');
                }

                if (Schema::hasColumn('departments', 'course_codes_assigned_at')) {
                    $table->dropColumn('course_codes_assigned_at');
                }

                if (Schema::hasColumn('departments', 'courses_submitted_by_user_id')) {
                    $table->dropConstrainedForeignId('courses_submitted_by_user_id');
                }

                if (Schema::hasColumn('departments', 'courses_submitted_to_cdc_at')) {
                    $table->dropColumn('courses_submitted_to_cdc_at');
                }
            });
        }
    }
};
