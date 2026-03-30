@extends('layouts.app')

@section('title', 'Scheme Details')

@section('content')
@php
    $requiredCourses = $department->courseBaskets->sum('courses');
    $designedCourses = $department->courses->count();
    $progress = $requiredCourses > 0 ? min(100, (int) round(($designedCourses / $requiredCourses) * 100)) : 0;
    $isSubmittedToCdc = $department->hasSubmittedCoursesToCdc();
    $areCourseCodesAssigned = $department->hasAssignedCourseCodes();
@endphp

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Scheme Details</h2>
            <p style="color: #6b7280;">Review assignment and course-design progress for <strong>{{ $department->name }}</strong>.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            @if($isSubmittedToCdc)
                <a href="{{ route('cdc.departments.course-codes.edit', $department) }}" class="btn btn-success">
                    {{ $areCourseCodesAssigned ? 'Update Course Codes' : 'Allocate Course Codes' }}
                </a>
            @endif
            <a href="{{ route('cdc.departments.assign', $department) }}" class="btn btn-primary">{{ $department->assigned_user_id ? 'Reassign Scheme' : 'Assign Scheme' }}</a>
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
            <div style="font-size: 13px; color: #6b7280;">Assigned To</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $department->assignedUser?->name ?? 'Not assigned' }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Course Progress</div>
            <div style="font-size: 20px; font-weight: 600;">{{ $designedCourses }}/{{ $requiredCourses }}</div>
        </div>
    </div>

    <div class="alert {{ $areCourseCodesAssigned ? 'alert-success' : ($isSubmittedToCdc ? 'alert-warning' : 'alert-warning') }}">
        @if($areCourseCodesAssigned)
            Department submitted the course design on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }} and CDC allocated course codes on {{ $department->course_codes_assigned_at?->format('d M Y, h:i A') }}.
        @elseif($isSubmittedToCdc)
            Department submitted the course design on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }}. CDC can now allocate course codes.
        @else
            The department has not submitted the designed courses to CDC yet.
        @endif
    </div>

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
