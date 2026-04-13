<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;

class CdcDashboardController extends Controller
{
    /**
     * Show the CDC dashboard.
     */
    public function index()
    {
        $programmes = Department::with(['courses', 'assignedUser'])->get();

        return view('cdc.dashboard', [
            'programmeCount' => $programmes->count(),
            'pendingAssignmentCount' => $programmes->whereNull('assigned_user_id')->count(),
            'pendingReviewCount' => $programmes->where('cdc_review_status', 'submitted')->count(),
            'approvedPendingCodesCount' => $programmes->where('cdc_review_status', 'approved')->count(),
            'accountCounts' => [
                'hod' => User::where('role', 'hod')->count(),
                'moderator' => User::where('role', 'moderator')->count(),
                'faculty' => User::where('role', 'faculty')->count(),
            ],
        ]);
    }
}
