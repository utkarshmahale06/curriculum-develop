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
        if (! Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'cdc_review_status')) {
                $table->string('cdc_review_status')->default('draft')->after('course_codes_assigned_by_user_id');
            }

            if (! Schema::hasColumn('departments', 'cdc_review_remarks')) {
                $table->text('cdc_review_remarks')->nullable()->after('cdc_review_status');
            }

            if (! Schema::hasColumn('departments', 'cdc_reviewed_at')) {
                $table->timestamp('cdc_reviewed_at')->nullable()->after('cdc_review_remarks');
            }

            if (! Schema::hasColumn('departments', 'cdc_reviewed_by_user_id')) {
                $table->foreignId('cdc_reviewed_by_user_id')
                    ->nullable()
                    ->after('cdc_reviewed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'cdc_reviewed_by_user_id')) {
                $table->dropConstrainedForeignId('cdc_reviewed_by_user_id');
            }

            if (Schema::hasColumn('departments', 'cdc_reviewed_at')) {
                $table->dropColumn('cdc_reviewed_at');
            }

            if (Schema::hasColumn('departments', 'cdc_review_remarks')) {
                $table->dropColumn('cdc_review_remarks');
            }

            if (Schema::hasColumn('departments', 'cdc_review_status')) {
                $table->dropColumn('cdc_review_status');
            }
        });
    }
};
