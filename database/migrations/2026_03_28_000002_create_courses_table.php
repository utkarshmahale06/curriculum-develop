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
        if (Schema::hasTable('courses')) {
            return;
        }

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_basket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('semester_name');
            $table->unsignedInteger('sr_no');
            $table->string('course_title');
            $table->string('abbreviation');
            $table->string('course_type');
            $table->string('course_code')->nullable();
            $table->unsignedInteger('total_iks_hours')->default(0);
            $table->unsignedInteger('cl')->nullable();
            $table->unsignedInteger('tl')->nullable();
            $table->unsignedInteger('ll')->nullable();
            $table->unsignedInteger('self_learning')->default(0);
            $table->unsignedInteger('notional_hours')->default(0);
            $table->unsignedInteger('credits')->default(0);
            $table->decimal('paper_duration', 4, 1)->nullable();
            $table->unsignedInteger('fa_th_max')->default(0);
            $table->unsignedInteger('sa_th_max')->default(0);
            $table->unsignedInteger('theory_total')->default(0);
            $table->unsignedInteger('theory_min')->default(0);
            $table->unsignedInteger('fa_pr_max')->default(0);
            $table->unsignedInteger('fa_pr_min')->default(0);
            $table->unsignedInteger('sa_pr_max')->default(0);
            $table->unsignedInteger('sa_pr_min')->default(0);
            $table->unsignedInteger('sla_max')->default(0);
            $table->unsignedInteger('sla_min')->default(0);
            $table->unsignedInteger('total_marks')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        Schema::dropIfExists('courses');
    }
};
