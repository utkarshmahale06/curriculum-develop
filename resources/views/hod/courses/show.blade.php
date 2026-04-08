@extends('layouts.app')

@section('title', 'Designed Courses')

@section('content')
@php
    $showCourseCodeColumn = $department->courses->contains(function ($course) {
        return filled($course->course_code)
            && ! \Illuminate\Support\Str::startsWith($course->course_code, ['DRAFT-', 'SUBMITTED-', 'PENDING-']);
    });
@endphp
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Designed Courses</h2>
            <p style="color: #6b7280;">Review the designed courses for <strong>{{ $department->name }}</strong>.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('hod.courses.edit', $department) }}" class="btn btn-primary">Edit Courses</a>
            <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if($department->courses->isEmpty())
        <div class="alert alert-warning">
            No courses have been designed for this scheme yet.
        </div>
    @else
        @foreach($coursesBySemester as $semester => $courses)
            <div style="margin-bottom: 26px;">
                <h3 style="font-size: 16px; margin-bottom: 12px;">{{ $semester }}</h3>
                <div style="overflow-x: auto; border-radius: 8px;">
                <table>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Basket</th>
                            @if($showCourseCodeColumn)
                                <th>Course Code</th>
                            @endif
                            <th>Course Title</th>
                            <th>Abbrev.</th>
                            <th>Course Type</th>
                            <th>IKS</th>
                            <th>CL</th>
                            <th>TL</th>
                            <th>LL</th>
                            <th>Self Learning</th>
                            <th>Notional Hrs</th>
                            <th>Credits</th>
                            <th>Paper Duration</th>
                            <th>FA-TH Max</th>
                            <th>SA-TH Max</th>
                            <th>Theory Total</th>
                            <th>Theory Min</th>
                            <th>FA-PR Max</th>
                            <th>FA-PR Min</th>
                            <th>SA-PR Max</th>
                            <th>SA-PR Min</th>
                            <th>SLA Max</th>
                            <th>SLA Min</th>
                            <th>Total Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($courses->sortBy('sr_no') as $course)
                            <tr>
                                <td>{{ $course->sr_no }}</td>
                                <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                                @if($showCourseCodeColumn)
                                    <td>{{ $course->course_code && ! \Illuminate\Support\Str::startsWith($course->course_code, ['DRAFT-', 'SUBMITTED-', 'PENDING-']) ? $course->course_code : 'Pending CDC allocation' }}</td>
                                @endif
                                <td>{{ $course->course_title }}</td>
                                <td>{{ $course->abbreviation }}</td>
                                <td>{{ $course->course_type }}</td>
                                <td>{{ $course->total_iks_hours }}</td>
                                <td>{{ $course->cl ?? 0 }}</td>
                                <td>{{ $course->tl ?? 0 }}</td>
                                <td>{{ $course->ll ?? 0 }}</td>
                                <td>{{ $course->self_learning ?? 0 }}</td>
                                <td>{{ $course->notional_hours }}</td>
                                <td>{{ $course->credits }}</td>
                                <td>{{ $course->paper_duration ?? '-' }}</td>
                                <td>{{ $course->fa_th_max }}</td>
                                <td>{{ $course->sa_th_max }}</td>
                                <td>{{ $course->theory_total }}</td>
                                <td>{{ $course->theory_min }}</td>
                                <td>{{ $course->fa_pr_max }}</td>
                                <td>{{ $course->fa_pr_min }}</td>
                                <td>{{ $course->sa_pr_max }}</td>
                                <td>{{ $course->sa_pr_min }}</td>
                                <td>{{ $course->sla_max }}</td>
                                <td>{{ $course->sla_min }}</td>
                                <td>{{ $course->total_marks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
