<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;

class ModeratorDashboardController extends Controller
{
    /**
     * Show the moderator dashboard.
     */
    public function index()
    {
        $facultyCourses = Course::query()
            ->with(['department.assignedUser', 'courseBasket', 'assignedFaculty'])
            ->whereNotNull('faculty_user_id')
            ->orderBy('semester_name')
            ->orderBy('sr_no')
            ->get();

        $facultyUsers = User::query()
            ->where('role', 'faculty')
            ->withCount('facultyCourses')
            ->orderBy('name')
            ->get();

        return view('moderator.dashboard', [
            'facultyCourses' => $facultyCourses,
            'facultyUsers' => $facultyUsers,
            'summary' => [
                'faculty_count' => $facultyUsers->count(),
                'assigned_subjects' => $facultyCourses->count(),
                'pending_syllabus' => $facultyCourses->count(),
                'approved_syllabus' => 0,
            ],
        ]);
    }
}
