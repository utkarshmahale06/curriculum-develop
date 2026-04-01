<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HodDashboardController extends Controller
{
    /**
     * Show the HOD dashboard.
     */
    public function index()
    {
        $department = Auth::user()
            ->linkedDepartment()
            ->with(['courseBaskets', 'courses.courseBasket', 'courses.assignedFaculty'])
            ->first();

        return view('hod.dashboard', compact('department'));
    }
}
