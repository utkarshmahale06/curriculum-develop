<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FacultyDashboardController extends Controller
{
    /**
     * Show the faculty dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $assignedCourses = $user->facultyCourses()
            ->with(['department', 'courseBasket'])
            ->orderBy('semester_name')
            ->orderBy('sr_no')
            ->get();

        // Derive department context from assigned courses (no longer stored on user)
        $department = $assignedCourses->first()?->department;

        return view('faculty.dashboard', compact('department', 'assignedCourses'));
    }
}
