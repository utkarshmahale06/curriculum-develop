<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CdcSchemeAssignmentController extends Controller
{
    /**
     * Show the scheme details screen.
     */
    public function show(Department $department)
    {
        $department->load(['assignedUser', 'courseBaskets', 'courses.courseBasket', 'courseSubmittedBy', 'courseCodesAssignedBy']);

        return view('cdc.departments.show', compact('department'));
    }

    /**
     * Show the assignment screen for a scheme.
     */
    public function edit(Department $department)
    {
        $departmentUsers = User::query()
            ->where('role', 'department')
            ->withCount('assignedDepartments')
            ->orderBy('name')
            ->get();

        return view('cdc.departments.assign', compact('department', 'departmentUsers'));
    }

    /**
     * Assign the selected scheme to a department user.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'assigned_user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::query()
            ->whereKey($validated['assigned_user_id'])
            ->where('role', 'department')
            ->first();

        if (! $user) {
            return back()->withErrors([
                'assigned_user_id' => 'Please select a valid department user.',
            ]);
        }

        $department->update([
            'assigned_user_id' => $user->id,
        ]);

        return redirect()->route('cdc.departments.index')
            ->with('success', "Scheme assigned to {$user->name} successfully.");
    }

    /**
     * Show the course-code allocation screen for CDC.
     */
    public function editCourseCodes(Department $department)
    {
        $department->load(['assignedUser', 'courseBaskets', 'courses.courseBasket']);

        if (! $department->hasSubmittedCoursesToCdc()) {
            return redirect()->route('cdc.departments.show', $department)
                ->with('error', 'The department must submit the designed courses before CDC can allocate course codes.');
        }

        return view('cdc.departments.course-codes', compact('department'));
    }

    /**
     * Save the CDC course codes for the submitted scheme.
     */
    public function updateCourseCodes(Request $request, Department $department)
    {
        $department->load('courses');

        if (! $department->hasSubmittedCoursesToCdc()) {
            return redirect()->route('cdc.departments.show', $department)
                ->with('error', 'The department must submit the designed courses before CDC can allocate course codes.');
        }

        $courseIds = $department->courses->pluck('id')->all();

        $validated = $request->validate([
            'course_codes' => ['required', 'array'],
            'course_codes.*' => ['required', 'string', 'max:50', 'distinct'],
        ], [
            'course_codes.*.required' => 'Enter a course code for each course.',
            'course_codes.*.distinct' => 'Course codes must be unique within the scheme.',
        ]);

        $submittedIds = collect(array_keys($validated['course_codes']))->map(fn ($id) => (int) $id)->sort()->values()->all();
        $expectedIds = collect($courseIds)->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($submittedIds !== $expectedIds) {
            return back()->withErrors([
                'course_codes' => 'Provide a course code for every submitted course.',
            ])->withInput();
        }

        DB::transaction(function () use ($department, $validated) {
            foreach ($department->courses as $course) {
                $course->update([
                    'course_code' => trim($validated['course_codes'][$course->id]),
                ]);
            }

            $assignmentData = [];

            if (Schema::hasColumn('departments', 'course_codes_assigned_at')) {
                $assignmentData['course_codes_assigned_at'] = now();
            }

            if (Schema::hasColumn('departments', 'course_codes_assigned_by_user_id')) {
                $assignmentData['course_codes_assigned_by_user_id'] = Auth::id();
            }

            if ($assignmentData !== []) {
                $department->update($assignmentData);
            }
        });

        return redirect()->route('cdc.departments.show', $department)
            ->with('success', 'Course codes allocated successfully.');
    }
}
