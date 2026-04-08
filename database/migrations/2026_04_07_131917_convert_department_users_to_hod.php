<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Convert any role='department' users to 'hod'
        DB::table('users')
            ->where('role', 'department')
            ->update(['role' => 'hod']);

        // 2. Ensure any user that is assigned to a department
        // as its assigned_user_id has that department as their department_id
        $assignments = DB::table('departments')
            ->whereNotNull('assigned_user_id')
            ->get();

        foreach ($assignments as $assignment) {
            DB::table('users')
                ->where('id', $assignment->assigned_user_id)
                ->whereNull('department_id')
                ->update(['department_id' => $assignment->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot reliably revert all 'hod' users back to 'department'
        // without knowing which were originally which, so this migration
        // is effectively a one-way role consolidation.
    }
};
