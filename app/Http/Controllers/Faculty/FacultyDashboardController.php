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
        $department = $user->linkedDepartment()->first();
        $assignedCourses = $user->facultyCourses()
            ->with(['department', 'courseBasket'])
            ->orderBy('semester_name')
            ->orderBy('sr_no')
            ->get();

        return view('faculty.dashboard', compact('department', 'assignedCourses'));
    }
}
