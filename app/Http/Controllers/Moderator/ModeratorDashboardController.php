<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class ModeratorDashboardController extends Controller
{
    /**
     * Show the moderator dashboard.
     */
    public function index()
    {
        $moderatorId = Auth::id();

        $assignedCourses = Course::query()
            ->with(['department.assignedUser', 'courseBasket', 'assignedFaculty'])
            ->where('moderator_user_id', $moderatorId)
            ->orderBy('semester_name')
            ->orderBy('sr_no')
            ->get();

        return view('moderator.dashboard', [
            'assignedCourses' => $assignedCourses,
            'summary' => [
                'assigned_subjects' => $assignedCourses->count(),
                'pending_syllabus' => $assignedCourses->count(),
                'approved_syllabus' => 0,
            ],
        ]);
    }
}
