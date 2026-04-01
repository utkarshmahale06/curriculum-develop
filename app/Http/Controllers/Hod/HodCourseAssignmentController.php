<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HodCourseAssignmentController extends Controller
{
    /**
     * Show the faculty assignment screen.
     */
    public function edit(Department $department)
    {
        $this->ensureManagedDepartment($department);

        $department->load(['courseBaskets', 'courses.courseBasket', 'courses.assignedFaculty']);

        $facultyUsers = User::query()
            ->where('role', 'faculty')
            ->where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        return view('hod.assign-faculty', compact('department', 'facultyUsers'));
    }

    /**
     * Save faculty assignments for courses.
     */
    public function update(Request $request, Department $department)
    {
        $this->ensureManagedDepartment($department);
        $department->load('courses');

        $facultyIds = User::query()
            ->where('role', 'faculty')
            ->where('department_id', $department->id)
            ->pluck('id')
            ->all();

        $courseIds = $department->courses->pluck('id')->all();

        $validated = $request->validate([
            'faculty_assignments' => ['required', 'array'],
            'faculty_assignments.*' => ['nullable', 'integer', 'in:' . implode(',', array_merge([0], $facultyIds))],
        ], [
            'faculty_assignments.required' => 'Provide faculty assignments for the available courses.',
            'faculty_assignments.*.in' => 'Select a valid faculty user for each course.',
        ]);

        $submittedIds = collect(array_keys($validated['faculty_assignments']))
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();
        $expectedIds = collect($courseIds)->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($submittedIds !== $expectedIds) {
            return back()->withErrors([
                'faculty_assignments' => 'Provide an assignment value for every course row.',
            ])->withInput();
        }

        DB::transaction(function () use ($department, $validated) {
            foreach ($department->courses as $course) {
                $course->update([
                    'faculty_user_id' => $validated['faculty_assignments'][$course->id] ?: null,
                ]);
            }
        });

        return redirect()->route('hod.dashboard')
            ->with('success', 'Faculty assignments updated successfully.');
    }

    /**
     * Ensure the HOD manages the given department.
     */
    protected function ensureManagedDepartment(Department $department): void
    {
        abort_unless((int) Auth::user()->department_id === (int) $department->id, 403);
    }
}
