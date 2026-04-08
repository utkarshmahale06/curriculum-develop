@extends('layouts.app')

@section('title', 'Scheme Details')

@section('content')
@php
    $requiredCourses = $department->courseBaskets->sum('courses');
    $designedCourses = $department->courses->count();
    $progress = $requiredCourses > 0 ? min(100, (int) round(($designedCourses / $requiredCourses) * 100)) : 0;
    $isSubmittedToCdc = $department->hasSubmittedCoursesToCdc();
    $areCourseCodesAssigned = $department->hasAssignedCourseCodes();
    $isApprovedByCdc = $department->isApprovedByCdc();
@endphp

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Programme Workflow</h2>
            <p style="color: #6b7280;">Review ownership, CDC approval, and course-design progress for <strong>{{ $department->name }}</strong>.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            @if($isApprovedByCdc)
                <a href="{{ route('cdc.departments.course-codes.edit', $department) }}" class="btn btn-success">
                    {{ $areCourseCodesAssigned ? 'Update Course Codes' : 'Allocate Course Codes' }}
                </a>
            @endif
            <a href="{{ route('cdc.departments.assign', $department) }}" class="btn btn-primary">{{ $department->assigned_user_id ? 'Reassign Owner' : 'Assign Owner' }}</a>
            <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Programme Code</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $department->code }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Year</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $department->year }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">HOD Owner</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $department->assignedUser?->name ?? 'Not assigned' }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Workflow Status</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $department->workflowLabel() }}</div>
        </div>
    </div>

    <div class="alert {{ $areCourseCodesAssigned ? 'alert-success' : ($department->cdc_review_status === 'revision_requested' ? 'alert-error' : 'alert-warning') }}">
        @if($areCourseCodesAssigned)
            HOD submitted the course design on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }}, CDC approved it, and course codes were allocated on {{ $department->course_codes_assigned_at?->format('d M Y, h:i A') }}.
        @elseif($department->cdc_review_status === 'revision_requested')
            CDC sent this programme back for revision{{ $department->cdc_review_remarks ? ': ' . $department->cdc_review_remarks : '.' }}
        @elseif($department->cdc_review_status === 'approved')
            CDC approved the submitted design{{ $department->cdc_review_remarks ? ' with note: ' . $department->cdc_review_remarks : '' }}. Course codes can now be allocated.
        @elseif($isSubmittedToCdc)
            HOD submitted the course design on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }}. Review and approve it before allocating course codes.
        @else
            The HOD has not submitted the designed courses to CDC yet.
        @endif
    </div>

    @if($isSubmittedToCdc && ! $areCourseCodesAssigned)
        <div class="card" style="margin-bottom: 24px; padding: 18px;">
            <h3 style="font-size: 16px; margin-bottom: 12px;">CDC Review</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <form method="POST" action="{{ route('cdc.departments.approve', $department) }}">
                    @csrf
                    <div class="form-group">
                        <label for="approve_remarks">Approval Note</label>
                        <textarea id="approve_remarks" name="cdc_review_remarks" rows="4" placeholder="Optional note for HOD">{{ old('cdc_review_remarks', $department->cdc_review_status === 'approved' ? $department->cdc_review_remarks : '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Approve Design</button>
                </form>
                <form method="POST" action="{{ route('cdc.departments.request-revision', $department) }}">
                    @csrf
                    <div class="form-group">
                        <label for="revision_remarks">Revision Remarks</label>
                        <textarea id="revision_remarks" name="cdc_review_remarks" rows="4" placeholder="Required remarks for revision">{{ old('cdc_review_remarks', $department->cdc_review_status === 'revision_requested' ? $department->cdc_review_remarks : '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Back For Revision</button>
                </form>
            </div>
        </div>
    @endif

    <div style="width: 100%; background: #e5e7eb; border-radius: 999px; height: 12px; overflow: hidden; margin-bottom: 24px;">
        <div style="width: {{ $progress }}%; background: {{ $progress === 100 ? '#16a34a' : '#2563eb' }}; height: 100%;"></div>
    </div>

    <h3 style="font-size: 16px; margin-bottom: 12px;">Basket Summary</h3>
    <table style="margin-bottom: 24px;">
        <thead>
            <tr>
                <th>Basket</th>
                <th>Required Courses</th>
                <th>Designed Courses</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($department->courseBaskets as $basket)
                @php
                    $basketDesigned = $department->courses->where('course_basket_id', $basket->id)->count();
                @endphp
                <tr>
                    <td>{{ $basket->basket_name }}</td>
                    <td>{{ $basket->courses }}</td>
                    <td>{{ $basketDesigned }}</td>
                    <td>{{ $basketDesigned >= $basket->courses ? 'Completed' : 'In Progress' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3 style="font-size: 16px; margin-bottom: 12px;">Designed Courses</h3>
    @if($department->courses->isEmpty())
        <p style="color: #6b7280;">No courses have been designed for this scheme yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Basket</th>
                    <th>Credits</th>
                    <th>Total Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($department->courses->sortBy(['semester_name', 'sr_no']) as $course)
                    <tr>
                        <td>{{ $course->semester_name }}</td>
                        <td>{{ $course->course_code ?? 'Pending CDC allocation' }}</td>
                        <td>{{ $course->course_title }}</td>
                        <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                        <td>{{ $course->credits }}</td>
                        <td>{{ $course->total_marks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
