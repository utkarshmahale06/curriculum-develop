<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DepartmentDashboardController extends Controller
{
    /**
     * Show the department dashboard.
     */
    public function index()
    {
        $assignedDepartments = Auth::user()
            ->assignedDepartments()
            ->with(['courseBaskets', 'courses'])
            ->orderBy('name')
            ->get();

        return view('department.dashboard', compact('assignedDepartments'));
    }
}
