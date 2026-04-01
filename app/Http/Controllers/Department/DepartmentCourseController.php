<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class DepartmentCourseController extends Controller
{
    /**
     * Show the designed courses in read-only mode.
     */
    public function show(Department $department)
    {
        $this->ensureAssignedDepartment($department);

        $department->load(['courseBaskets', 'courses.courseBasket']);

        return view('department.courses.show', [
            'department' => $department,
            'coursesBySemester' => $department->courses
                ->sortBy(['semester_name', 'sr_no'])
                ->groupBy('semester_name'),
        ]);
    }

    /**
     * Show the course design screen for an assigned scheme.
     */
    public function edit(Department $department)
    {
        $this->ensureAssignedDepartment($department);

        $department->load(['courseBaskets', 'courses.courseBasket']);

        return view('department.courses.edit', [
            'department' => $department,
            'basketOptions' => $department->courseBaskets()->orderBy('id')->get(),
        ]);
    }

    /**
     * Save the designed courses for an assigned scheme.
     */
    public function update(Request $request, Department $department)
    {
        $this->ensureAssignedDepartment($department);

        $saveMode = $request->input('save_mode') === 'draft' ? 'draft' : 'final';
        $basketOptions = $department->courseBaskets()->orderBy('id')->get();
        $basketIds = $basketOptions->pluck('id')->all();
        $basketsById = $basketOptions->keyBy('id');
        $existingCourseIds = $department->courses()->pluck('id')->all();

        $validator = validator($request->all(), [
            'courses' => ['required', 'array', 'min:1'],
            'courses.*.id' => ['nullable', 'integer', 'in:' . implode(',', array_merge([0], $existingCourseIds))],
            'courses.*.course_basket_id' => ['required', 'integer', 'in:' . implode(',', $basketIds)],
            'courses.*.semester_name' => ['required', 'string', 'max:20'],
            'courses.*.sr_no' => ['required', 'integer', 'min:1'],
            'courses.*.course_title' => ['required', 'string', 'max:255'],
            'courses.*.abbreviation' => ['required', 'string', 'max:50'],
            'courses.*.course_type' => ['nullable', 'string', 'max:50'],
            'courses.*.course_code' => ['nullable', 'string', 'max:50'],
            'courses.*.total_iks_hours' => ['nullable', 'integer', 'min:0'],
            'courses.*.cl' => ['nullable', 'integer', 'min:0'],
            'courses.*.tl' => ['nullable', 'integer', 'min:0'],
            'courses.*.ll' => ['nullable', 'integer', 'min:0'],
            'courses.*.self_learning' => ['nullable', 'integer', 'min:0'],
            'courses.*.credits' => ['required', 'integer', 'min:1'],
            'courses.*.paper_duration' => ['nullable', 'numeric', 'min:0'],
            'courses.*.fa_th_max' => ['nullable', 'integer', 'min:0'],
            'courses.*.sa_th_max' => ['nullable', 'integer', 'min:0'],
            'courses.*.theory_min' => ['nullable', 'integer', 'min:0'],
            'courses.*.fa_pr_max' => ['nullable', 'integer', 'min:0'],
            'courses.*.fa_pr_min' => ['nullable', 'integer', 'min:0'],
            'courses.*.sa_pr_max' => ['nullable', 'integer', 'min:0'],
            'courses.*.sa_pr_min' => ['nullable', 'integer', 'min:0'],
            'courses.*.sla_max' => ['nullable', 'integer', 'min:0'],
            'courses.*.sla_min' => ['nullable', 'integer', 'min:0'],
        ], [
            'courses.required' => 'Add at least one course row.',
            'courses.*.course_basket_id.required' => 'Select a basket for each course.',
            'courses.*.semester_name.required' => 'Semester is required for each course.',
            'courses.*.course_title.required' => 'Course title is required for each course.',
        ]);

        $validator->after(function (Validator $validator) use ($request, $basketOptions, $saveMode) {
            $courses = $request->input('courses', []);
            $counts = collect($courses)->countBy('course_basket_id');

            foreach ($basketOptions as $basket) {
                $designedCount = (int) ($counts[$basket->id] ?? 0);

                if ($designedCount > $basket->courses) {
                    $validator->errors()->add(
                        'courses',
                        "Basket {$basket->basket_name} allows {$basket->courses} courses, but {$designedCount} were entered."
                    );
                }
                $basketCourses = collect($courses)->where('course_basket_id', $basket->id);

                if ($basketCourses->isEmpty()) {
                    continue;
                }

                if ($saveMode === 'draft') {
                    continue;
                }

                $enteredCl = (int) $basketCourses->sum(fn ($course) => (int) ($course['cl'] ?? 0));
                $enteredTl = (int) $basketCourses->sum(fn ($course) => (int) ($course['tl'] ?? 0));
                $enteredLl = (int) $basketCourses->sum(fn ($course) => (int) ($course['ll'] ?? 0));
                $enteredHours = $enteredCl + $enteredTl + $enteredLl;
                $enteredCredits = (int) $basketCourses->sum(fn ($course) => (int) ($course['credits'] ?? 0));
                $enteredMarks = (int) $basketCourses->sum(function ($course) {
                    return (int) ($course['fa_th_max'] ?? 0)
                        + (int) ($course['sa_th_max'] ?? 0)
                        + (int) ($course['fa_pr_max'] ?? 0)
                        + (int) ($course['sa_pr_max'] ?? 0)
                        + (int) ($course['sla_max'] ?? 0);
                });

                if ($enteredCl !== (int) ($basket->cl ?? 0)) {
                    $validator->errors()->add('courses', "Total CL for basket {$basket->basket_name} must be {$basket->cl}.");
                }

                if ($enteredTl !== (int) ($basket->tl ?? 0)) {
                    $validator->errors()->add('courses', "Total TL for basket {$basket->basket_name} must be {$basket->tl}.");
                }

                if ($enteredLl !== (int) ($basket->ll ?? 0)) {
                    $validator->errors()->add('courses', "Total LL for basket {$basket->basket_name} must be {$basket->ll}.");
                }

                if ($enteredHours !== (int) $basket->hours) {
                    $validator->errors()->add('courses', "Total hours for basket {$basket->basket_name} must be {$basket->hours}.");
                }

                if ($enteredCredits !== (int) $basket->credits) {
                    $validator->errors()->add('courses', "Total credits for basket {$basket->basket_name} must be {$basket->credits}.");
                }

                if ($enteredMarks !== (int) $basket->marks) {
                    $validator->errors()->add('courses', "Total marks for basket {$basket->basket_name} must be {$basket->marks}.");
                }
            }
        });

        $validated = $validator->validate();

        DB::transaction(function () use ($department, $validated, $basketsById) {
            $existingCourses = $department->courses()->get()->keyBy('id');
            $retainedCourseIds = [];
            $hasLegacyCreatedByUserId = Schema::hasColumn('courses', 'created_by_user_id');
            $hasLegacyHours = Schema::hasColumn('courses', 'hours');
            $hasLegacyMarks = Schema::hasColumn('courses', 'marks');
            $supportsNullableCourseCode = $this->supportsNullableCourseCode();
            $hasCoursesSubmittedToCdcAt = Schema::hasColumn('departments', 'courses_submitted_to_cdc_at');
            $hasCoursesSubmittedByUserId = Schema::hasColumn('departments', 'courses_submitted_by_user_id');
            $hasCourseCodesAssignedAt = Schema::hasColumn('departments', 'course_codes_assigned_at');
            $hasCourseCodesAssignedByUserId = Schema::hasColumn('departments', 'course_codes_assigned_by_user_id');
            $hasCdcReviewStatus = Schema::hasColumn('departments', 'cdc_review_status');
            $hasCdcReviewRemarks = Schema::hasColumn('departments', 'cdc_review_remarks');
            $hasCdcReviewedAt = Schema::hasColumn('departments', 'cdc_reviewed_at');
            $hasCdcReviewedByUserId = Schema::hasColumn('departments', 'cdc_reviewed_by_user_id');

            foreach ($validated['courses'] as $course) {
                $existingCourse = isset($course['id']) ? $existingCourses->get((int) $course['id']) : null;
                $basket = $basketsById->get((int) $course['course_basket_id']);
                $cl = (int) ($course['cl'] ?? 0);
                $tl = (int) ($course['tl'] ?? 0);
                $ll = (int) ($course['ll'] ?? 0);
                $selfLearning = (int) ($course['self_learning'] ?? 0);
                $faThMax = (int) ($course['fa_th_max'] ?? 0);
                $saThMax = (int) ($course['sa_th_max'] ?? 0);
                $faPrMax = (int) ($course['fa_pr_max'] ?? 0);
                $saPrMax = (int) ($course['sa_pr_max'] ?? 0);
                $slaMax = (int) ($course['sla_max'] ?? 0);
                $courseData = [
                    'course_basket_id' => $course['course_basket_id'],
                    'created_by' => Auth::id(),
                    'semester_name' => $course['semester_name'],
                    'sr_no' => $course['sr_no'],
                    'course_title' => $course['course_title'],
                    'abbreviation' => $course['abbreviation'],
                    'course_type' => $basket?->basket_name,
                    'course_code' => $existingCourse?->course_code
                        ?? ($supportsNullableCourseCode ? null : $this->legacyWorkflowCourseCode($department, $course, 'DRAFT')),
                    'total_iks_hours' => $course['total_iks_hours'] ?: 0,
                    'cl' => $course['cl'] !== null && $course['cl'] !== '' ? (int) $course['cl'] : null,
                    'tl' => $course['tl'] !== null && $course['tl'] !== '' ? (int) $course['tl'] : null,
                    'll' => $course['ll'] !== null && $course['ll'] !== '' ? (int) $course['ll'] : null,
                    'self_learning' => $course['self_learning'] ?: 0,
                    'notional_hours' => $cl + $tl + $ll + $selfLearning,
                    'credits' => (int) $course['credits'],
                    'paper_duration' => $course['paper_duration'] ?: null,
                    'fa_th_max' => $course['fa_th_max'] ?: 0,
                    'sa_th_max' => $course['sa_th_max'] ?: 0,
                    'theory_total' => $faThMax + $saThMax,
                    'theory_min' => $course['theory_min'] ?: 0,
                    'fa_pr_max' => $course['fa_pr_max'] ?: 0,
                    'fa_pr_min' => $course['fa_pr_min'] ?: 0,
                    'sa_pr_max' => $course['sa_pr_max'] ?: 0,
                    'sa_pr_min' => $course['sa_pr_min'] ?: 0,
                    'sla_max' => $course['sla_max'] ?: 0,
                    'sla_min' => $course['sla_min'] ?: 0,
                    'total_marks' => $faThMax + $saThMax + $faPrMax + $saPrMax + $slaMax,
                ];

                if ($hasLegacyCreatedByUserId) {
                    $courseData['created_by_user_id'] = Auth::id();
                }

                if ($hasLegacyHours) {
                    $courseData['hours'] = $cl + $tl + $ll;
                }

                if ($hasLegacyMarks) {
                    $courseData['marks'] = $courseData['total_marks'];
                }

                if ($existingCourse) {
                    $existingCourse->update($courseData);
                    $retainedCourseIds[] = $existingCourse->id;
                } else {
                    $newCourse = $department->courses()->create($courseData);
                    $retainedCourseIds[] = $newCourse->id;
                }
            }

            $coursesToDelete = $existingCourses->keys()->diff($retainedCourseIds);
            if ($coursesToDelete->isNotEmpty()) {
                $department->courses()->whereIn('id', $coursesToDelete->all())->delete();
            }

            $hasAssignedCodes = $department->fresh('courses')->hasAssignedCourseCodes();

            if (! $hasAssignedCodes) {
                $statusReset = [];

                if ($hasCoursesSubmittedToCdcAt) {
                    $statusReset['courses_submitted_to_cdc_at'] = null;
                }

                if ($hasCoursesSubmittedByUserId) {
                    $statusReset['courses_submitted_by_user_id'] = null;
                }

                if ($hasCourseCodesAssignedAt) {
                    $statusReset['course_codes_assigned_at'] = null;
                }

                if ($hasCourseCodesAssignedByUserId) {
                    $statusReset['course_codes_assigned_by_user_id'] = null;
                }

                if ($hasCdcReviewStatus) {
                    $statusReset['cdc_review_status'] = 'draft';
                }

                if ($hasCdcReviewRemarks) {
                    $statusReset['cdc_review_remarks'] = null;
                }

                if ($hasCdcReviewedAt) {
                    $statusReset['cdc_reviewed_at'] = null;
                }

                if ($hasCdcReviewedByUserId) {
                    $statusReset['cdc_reviewed_by_user_id'] = null;
                }

                if ($statusReset !== []) {
                    $department->update($statusReset);
                }
            }
        });

        return redirect()->route('department.dashboard')
            ->with('success', $saveMode === 'draft'
                ? 'Course draft saved successfully. You can continue later.'
                : 'Courses designed successfully for the assigned scheme.');
    }

    /**
     * Submit the designed courses to CDC for course-code allocation.
     */
    public function submitToCdc(Department $department)
    {
        $this->ensureAssignedDepartment($department);

        $department->load(['courseBaskets', 'courses']);
        $errors = $this->submissionValidationErrors($department);

        if ($errors !== []) {
            return redirect()->route('department.dashboard')
                ->with('error', $errors[0]);
        }

        $submissionData = [];

        if (Schema::hasColumn('departments', 'courses_submitted_to_cdc_at')) {
            $submissionData['courses_submitted_to_cdc_at'] = now();
        }

        if (Schema::hasColumn('departments', 'courses_submitted_by_user_id')) {
            $submissionData['courses_submitted_by_user_id'] = Auth::id();
        }

        if (Schema::hasColumn('departments', 'course_codes_assigned_at')) {
            $submissionData['course_codes_assigned_at'] = null;
        }

        if (Schema::hasColumn('departments', 'course_codes_assigned_by_user_id')) {
            $submissionData['course_codes_assigned_by_user_id'] = null;
        }

        if (Schema::hasColumn('departments', 'cdc_review_status')) {
            $submissionData['cdc_review_status'] = 'submitted';
        }

        if (Schema::hasColumn('departments', 'cdc_review_remarks')) {
            $submissionData['cdc_review_remarks'] = null;
        }

        if (Schema::hasColumn('departments', 'cdc_reviewed_at')) {
            $submissionData['cdc_reviewed_at'] = null;
        }

        if (Schema::hasColumn('departments', 'cdc_reviewed_by_user_id')) {
            $submissionData['cdc_reviewed_by_user_id'] = null;
        }

        if ($submissionData !== []) {
            $department->update($submissionData);
        }

        if ($submissionData === []) {
            foreach ($department->courses as $course) {
                $course->update([
                    'course_code' => $this->legacyWorkflowCourseCode($department, [
                        'semester_name' => $course->semester_name,
                        'sr_no' => $course->sr_no,
                    ], 'SUBMITTED'),
                ]);
            }
        }

        return redirect()->route('department.dashboard')
            ->with('success', 'Courses submitted to CDC for course-code allocation.');
    }

    /**
     * Ensure the scheme belongs to the logged-in department user.
     */
    protected function ensureAssignedDepartment(Department $department): void
    {
        abort_unless($department->assigned_user_id === Auth::id(), 403);
    }

    /**
     * Check whether the saved courses are ready for CDC submission.
     *
     * @return list<string>
     */
    protected function submissionValidationErrors(Department $department): array
    {
        if ($department->courses->isEmpty()) {
            return ['Design at least one course before submitting to CDC.'];
        }

        $errors = [];
        $requiredCourseCount = (int) $department->courseBaskets->sum('courses');
        $designedCourseCount = (int) $department->courses->count();

        if ($designedCourseCount !== $requiredCourseCount) {
            $errors[] = "All {$requiredCourseCount} courses must be designed before submitting to CDC.";
        }

        foreach ($department->courseBaskets as $basket) {
            $basketCourses = $department->courses->where('course_basket_id', $basket->id);
            $basketCount = $basketCourses->count();

            if ($basketCount !== (int) $basket->courses) {
                $errors[] = "Basket {$basket->basket_name} must contain exactly {$basket->courses} designed courses before submission.";
                continue;
            }

            $enteredCl = (int) $basketCourses->sum(fn (Course $course) => (int) ($course->cl ?? 0));
            $enteredTl = (int) $basketCourses->sum(fn (Course $course) => (int) ($course->tl ?? 0));
            $enteredLl = (int) $basketCourses->sum(fn (Course $course) => (int) ($course->ll ?? 0));
            $enteredHours = $enteredCl + $enteredTl + $enteredLl;
            $enteredCredits = (int) $basketCourses->sum(fn (Course $course) => (int) ($course->credits ?? 0));
            $enteredMarks = (int) $basketCourses->sum(fn (Course $course) => (int) ($course->total_marks ?? 0));

            if ($enteredCl !== (int) ($basket->cl ?? 0)) {
                $errors[] = "Total CL for basket {$basket->basket_name} must be {$basket->cl} before submission.";
            }

            if ($enteredTl !== (int) ($basket->tl ?? 0)) {
                $errors[] = "Total TL for basket {$basket->basket_name} must be {$basket->tl} before submission.";
            }

            if ($enteredLl !== (int) ($basket->ll ?? 0)) {
                $errors[] = "Total LL for basket {$basket->basket_name} must be {$basket->ll} before submission.";
            }

            if ($enteredHours !== (int) $basket->hours) {
                $errors[] = "Total hours for basket {$basket->basket_name} must be {$basket->hours} before submission.";
            }

            if ($enteredCredits !== (int) $basket->credits) {
                $errors[] = "Total credits for basket {$basket->basket_name} must be {$basket->credits} before submission.";
            }

            if ($enteredMarks !== (int) $basket->marks) {
                $errors[] = "Total marks for basket {$basket->basket_name} must be {$basket->marks} before submission.";
            }
        }

        return $errors;
    }

    /**
     * Check whether the live schema allows empty course codes.
     */
    protected function supportsNullableCourseCode(): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return true;
        }

        $column = DB::selectOne("SHOW COLUMNS FROM courses LIKE 'course_code'");

        return isset($column->Null) && strtoupper((string) $column->Null) === 'YES';
    }

    /**
     * Provide a temporary legacy code when the database still requires one.
     */
    protected function legacyWorkflowCourseCode(Department $department, array $course, string $status): string
    {
        $departmentCode = Str::upper(preg_replace('/[^A-Z0-9]+/', '', (string) ($department->code ?: 'DEPT')));
        $semester = Str::upper(preg_replace('/[^A-Z0-9]+/', '', (string) ($course['semester_name'] ?? 'SEM')));
        $serial = str_pad((string) ((int) ($course['sr_no'] ?? 0)), 3, '0', STR_PAD_LEFT);

        return "{$status}-{$departmentCode}-{$semester}-{$serial}";
    }
}
