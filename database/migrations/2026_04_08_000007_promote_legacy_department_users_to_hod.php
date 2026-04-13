<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('departments')) {
            return;
        }

        $legacyUsers = DB::table('users')
            ->select('id', 'department_id')
            ->where('role', 'department')
            ->get();

        foreach ($legacyUsers as $user) {
            $ownedDepartmentId = DB::table('departments')
                ->where('assigned_user_id', $user->id)
                ->value('id');

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'role' => 'hod',
                    'department_id' => $user->department_id ?: $ownedDepartmentId,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy account promotion is intentionally irreversible.
    }
};
