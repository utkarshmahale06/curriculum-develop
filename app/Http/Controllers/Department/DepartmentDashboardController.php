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

        return view('department.dashboard', [
            'assignedDepartments' => $assignedDepartments,
            'summary' => [
                'total' => $assignedDepartments->count(),
                'draft' => $assignedDepartments->where('cdc_review_status', 'draft')->count(),
                'submitted' => $assignedDepartments->where('cdc_review_status', 'submitted')->count(),
                'revision_requested' => $assignedDepartments->where('cdc_review_status', 'revision_requested')->count(),
                'approved' => $assignedDepartments->where('cdc_review_status', 'approved')->count(),
                'codes_assigned' => $assignedDepartments->where('cdc_review_status', 'codes_assigned')->count(),
            ],
        ]);
    }
}
