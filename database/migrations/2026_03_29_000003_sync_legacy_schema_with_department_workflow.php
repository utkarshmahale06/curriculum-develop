<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->syncDepartmentsTable();
        $this->syncCoursesTable();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    /**
     * Align the departments table with the current workflow.
     */
    protected function syncDepartmentsTable(): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        if (! Schema::hasColumn('departments', 'assigned_user_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->foreignId('assigned_user_id')
                    ->nullable()
                    ->after('award_class_subjects')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('departments', 'assigned_cdc_department_user_id')) {
            DB::statement('
                UPDATE departments
                SET assigned_user_id = COALESCE(assigned_user_id, assigned_cdc_department_user_id)
                WHERE assigned_cdc_department_user_id IS NOT NULL
            ');
        }
    }

    /**
     * Align the legacy courses table with the current department course designer.
     */
    protected function syncCoursesTable(): void
    {
        if (! Schema::hasTable('courses')) {
            return;
        }

        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('course_basket_id');
            }

            if (! Schema::hasColumn('courses', 'semester_name')) {
                $table->string('semester_name')->nullable()->after('created_by');
            }

            if (! Schema::hasColumn('courses', 'sr_no')) {
                $table->unsignedInteger('sr_no')->nullable()->after('semester_name');
            }

            if (! Schema::hasColumn('courses', 'abbreviation')) {
                $table->string('abbreviation')->nullable()->after('course_title');
            }

            if (! Schema::hasColumn('courses', 'total_iks_hours')) {
                $table->unsignedInteger('total_iks_hours')->default(0)->after('course_code');
            }

            if (! Schema::hasColumn('courses', 'self_learning')) {
                $table->unsignedInteger('self_learning')->default(0)->after('ll');
            }

            if (! Schema::hasColumn('courses', 'notional_hours')) {
                $table->unsignedInteger('notional_hours')->default(0)->after('self_learning');
            }

            if (! Schema::hasColumn('courses', 'paper_duration')) {
                $table->decimal('paper_duration', 4, 1)->nullable()->after('credits');
            }

            if (! Schema::hasColumn('courses', 'fa_th_max')) {
                $table->unsignedInteger('fa_th_max')->default(0)->after('paper_duration');
            }

            if (! Schema::hasColumn('courses', 'sa_th_max')) {
                $table->unsignedInteger('sa_th_max')->default(0)->after('fa_th_max');
            }

            if (! Schema::hasColumn('courses', 'theory_total')) {
                $table->unsignedInteger('theory_total')->default(0)->after('sa_th_max');
            }

            if (! Schema::hasColumn('courses', 'theory_min')) {
                $table->unsignedInteger('theory_min')->default(0)->after('theory_total');
            }

            if (! Schema::hasColumn('courses', 'fa_pr_max')) {
                $table->unsignedInteger('fa_pr_max')->default(0)->after('theory_min');
            }

            if (! Schema::hasColumn('courses', 'fa_pr_min')) {
                $table->unsignedInteger('fa_pr_min')->default(0)->after('fa_pr_max');
            }

            if (! Schema::hasColumn('courses', 'sa_pr_max')) {
                $table->unsignedInteger('sa_pr_max')->default(0)->after('fa_pr_min');
            }

            if (! Schema::hasColumn('courses', 'sa_pr_min')) {
                $table->unsignedInteger('sa_pr_min')->default(0)->after('sa_pr_max');
            }

            if (! Schema::hasColumn('courses', 'sla_max')) {
                $table->unsignedInteger('sla_max')->default(0)->after('sa_pr_min');
            }

            if (! Schema::hasColumn('courses', 'sla_min')) {
                $table->unsignedInteger('sla_min')->default(0)->after('sla_max');
            }

            if (! Schema::hasColumn('courses', 'total_marks')) {
                $table->unsignedInteger('total_marks')->default(0)->after('sla_min');
            }
        });

        if (Schema::hasColumn('courses', 'created_by_user_id')) {
            DB::statement('
                UPDATE courses
                SET created_by = COALESCE(created_by, created_by_user_id)
                WHERE created_by_user_id IS NOT NULL
            ');
        }

        DB::statement("
            UPDATE courses
            SET
                semester_name = COALESCE(semester_name, 'I-Sem'),
                sr_no = COALESCE(sr_no, id),
                abbreviation = COALESCE(abbreviation, course_code),
                theory_total = COALESCE(theory_total, 0)
        ");

        if (Schema::hasColumn('courses', 'hours')) {
            DB::statement("
                UPDATE courses
                SET notional_hours = COALESCE(notional_hours, COALESCE(hours, 0))
            ");
        }

        if (Schema::hasColumn('courses', 'marks')) {
            DB::statement("
                UPDATE courses
                SET total_marks = COALESCE(total_marks, COALESCE(marks, 0))
            ");
        }
    }
};
