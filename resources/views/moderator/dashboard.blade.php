@extends('layouts.app')

@section('title', 'Moderator Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Moderator Dashboard</h2>
            <p style="color: #6b7280;">Welcome, {{ Auth::user()->name }}. Review the subjects assigned to you for moderation.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Assigned Subjects</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $summary['assigned_subjects'] }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Pending Syllabus Review</div>
            <div style="font-size: 24px; font-weight: 600; color: #92400e;">{{ $summary['pending_syllabus'] }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Approved Syllabus</div>
            <div style="font-size: 24px; font-weight: 600; color: #166534;">{{ $summary['approved_syllabus'] }}</div>
        </div>
    </div>

    <h3 style="font-size: 16px; margin-bottom: 12px;">My Assigned Subjects</h3>
    @if($assignedCourses->isEmpty())
        <div class="alert alert-warning">
            No subjects assigned for moderation.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Programme</th>
                    <th>HOD</th>
                    <th>Semester</th>
                    <th>Course</th>
                    <th>Faculty</th>
                    <th>Syllabus Status</th>
                    <th>Moderator Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedCourses as $course)
                    <tr>
                        <td>{{ $course->department?->name ?? '-' }}</td>
                        <td>{{ $course->department?->assignedUser?->name ?? '-' }}</td>
                        <td>{{ $course->semester_name }}</td>
                        <td>{{ $course->course_title }}</td>
                        <td>{{ $course->assignedFaculty?->name ?? 'Not assigned' }}</td>
                        <td>Pending syllabus generation</td>
                        <td>
                            <button type="button" class="btn btn-secondary" disabled>Review Coming Soon</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
