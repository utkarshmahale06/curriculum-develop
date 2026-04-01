@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">HOD Dashboard</h2>
            <p style="color: #6b7280;">Welcome, {{ Auth::user()->name }}. Review the department scheme and assign faculty to subjects.</p>
        </div>
    </div>

    @if(! $department)
        <div class="alert alert-warning">
            No programme/department is linked to this HOD account yet.
        </div>
    @else
        @php
            $requiredCourses = $department->courseBaskets->sum('courses');
            $designedCourses = $department->courses->count();
            $assignedFacultyCourses = $department->courses->filter(fn ($course) => $course->faculty_user_id)->count();
        @endphp

        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px;">
            <div class="card" style="padding: 16px;">
                <div style="font-size: 13px; color: #6b7280;">Programme</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $department->name }}</div>
            </div>
            <div class="card" style="padding: 16px;">
                <div style="font-size: 13px; color: #6b7280;">Code / Year</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $department->code }} / {{ $department->year }}</div>
            </div>
            <div class="card" style="padding: 16px;">
                <div style="font-size: 13px; color: #6b7280;">Designed Courses</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $designedCourses }}/{{ $requiredCourses }}</div>
            </div>
            <div class="card" style="padding: 16px;">
                <div style="font-size: 13px; color: #6b7280;">Faculty Assigned</div>
                <div style="font-size: 18px; font-weight: 600;">{{ $assignedFacultyCourses }}/{{ $designedCourses }}</div>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 22px;">
            <a href="{{ route('hod.faculty-assignments.edit', $department) }}" class="btn btn-primary">Assign Faculty</a>
        </div>

        <h3 style="font-size: 16px; margin-bottom: 12px;">Course Baskets</h3>
        <table style="margin-bottom: 24px;">
            <thead>
                <tr>
                    <th>Basket</th>
                    <th>Courses</th>
                    <th>CL</th>
                    <th>TL</th>
                    <th>LL</th>
                    <th>Hours</th>
                    <th>Credits</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($department->courseBaskets as $basket)
                    <tr>
                        <td>{{ $basket->basket_name }}</td>
                        <td>{{ $basket->courses }}</td>
                        <td>{{ $basket->cl ?? 0 }}</td>
                        <td>{{ $basket->tl ?? 0 }}</td>
                        <td>{{ $basket->ll ?? 0 }}</td>
                        <td>{{ $basket->hours }}</td>
                        <td>{{ $basket->credits }}</td>
                        <td>{{ $basket->marks }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3 style="font-size: 16px; margin-bottom: 12px;">Designed Courses</h3>
        @if($department->courses->isEmpty())
            <div class="alert alert-warning">
                The department has not designed any courses yet.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Sr No</th>
                        <th>Course Title</th>
                        <th>Basket</th>
                        <th>Course Code</th>
                        <th>Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->courses->sortBy(['semester_name', 'sr_no']) as $course)
                        <tr>
                            <td>{{ $course->semester_name }}</td>
                            <td>{{ $course->sr_no }}</td>
                            <td>{{ $course->course_title }}</td>
                            <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                            <td>{{ $course->course_code ?? 'Pending CDC allocation' }}</td>
                            <td>{{ $course->assignedFaculty?->name ?? 'Not assigned' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif
</div>
@endsection
