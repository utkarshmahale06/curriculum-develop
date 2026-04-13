<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HodDashboardController extends Controller
{
    /**
     * Show the HOD dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // All assigned schemes — HOD designs courses and assigns faculty for these
        $assignedDepartments = $user
            ->assignedDepartments()
            ->with(['courseBaskets', 'courses.courseBasket', 'courses.assignedFaculty', 'facultyUsers'])
            ->orderBy('name')
            ->get();

        return view('hod.dashboard', [
            'assignedDepartments' => $assignedDepartments,
            'moderators' => User::where('role', 'moderator')->orderBy('name')->get(),
            'facultyUsers' => User::where('role', 'faculty')->withCount('facultyCourses')->orderBy('name')->get(),
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
