@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">HOD Dashboard</h2>
            <p style="color: #6b7280;">Welcome, {{ Auth::user()->name }}. Manage your assigned programmes — design courses and assign faculty.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 30px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Assigned Programmes</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $summary['total'] }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Drafted Designs</div>
            <div style="font-size: 24px; font-weight: 600; color: #eab308;">{{ $summary['draft'] }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Submitted for Review</div>
            <div style="font-size: 24px; font-weight: 600; color: #3b82f6;">{{ $summary['submitted'] }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Action Required</div>
            <div style="font-size: 24px; font-weight: 600; color: #ef4444;">{{ $summary['revision_requested'] }}</div>
        </div>
    </div>

    <h3 style="font-size: 18px; margin-bottom: 16px;">Assigned Programmes</h3>

    @if($assignedDepartments->isEmpty())
        <div class="alert alert-warning" style="margin-bottom: 30px;">
            No programmes have been assigned to you yet.
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; margin-bottom: 30px;">
            @foreach($assignedDepartments as $department)
                @php
                    $requiredCourses = $department->courseBaskets->sum('courses');
                    $designedCourses = $department->courses->count();
                    $isComplete = $designedCourses === $requiredCourses;
                    $assignedFacultyCourses = $department->courses->filter(fn ($course) => $course->faculty_user_id)->count();
                    $statusColor = match($department->cdc_review_status) {
                        'draft' => '#eab308',
                        'submitted' => '#3b82f6',
                        'approved' => '#10b981',
                        'codes_assigned' => '#10b981',
                        'revision_requested' => '#ef4444',
                        default => '#6b7280'
                    };
                @endphp
                <div class="card" style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <h4 style="font-size: 16px; margin: 0;">{{ $department->name }}</h4>
                        <span style="font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 4px; background: {{ $statusColor }}20; color: {{ $statusColor }};">
                            {{ $department->workflowLabel() }}
                        </span>
                    </div>

                    <div style="font-size: 14px; color: #4b5563; margin-bottom: 16px;">
                        Year: {{ $department->year }} | Code: {{ $department->code }}
                    </div>

                    @if($department->cdc_review_status === 'revision_requested' && count($department->courses) > 0)
                        <div class="alert alert-error" style="font-size: 13px; padding: 10px; margin-bottom: 16px;">
                            <strong>Revision Requested:</strong> Edit your courses and submit again.
                        </div>
                    @endif

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
                            <span>Course Design: {{ $designedCourses }}/{{ $requiredCourses }}</span>
                            <span>{{ $isComplete ? '100%' : round(($designedCourses / max(1, $requiredCourses)) * 100) . '%' }}</span>
                        </div>
                        <div style="width: 100%; background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="background: {{ $isComplete ? '#10b981' : '#3b82f6' }}; height: 100%; width: {{ ($designedCourses / max(1, $requiredCourses)) * 100 }}%;"></div>
                        </div>
                    </div>

                    @if($department->cdc_review_status === 'codes_assigned' && $designedCourses > 0)
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 16px; color: #6b7280;">
                            <span>Faculty Assigned: {{ $assignedFacultyCourses }}/{{ $designedCourses }}</span>
                            <span>{{ $designedCourses > 0 ? round(($assignedFacultyCourses / $designedCourses) * 100) . '%' : '0%' }}</span>
                        </div>
                    @else
                        <div style="height: 16px;"></div>
                    @endif

                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        @if($department->cdc_review_status === 'draft' || $department->cdc_review_status === 'revision_requested')
                            <a href="{{ route('hod.courses.edit', $department) }}" class="btn btn-primary" style="flex: 1;">
                                {{ $designedCourses > 0 ? 'Edit Courses' : 'Design Remaining Courses' }}
                            </a>
                            @if($designedCourses > 0)
                                <form method="POST" action="{{ route('hod.courses.submit', $department) }}" style="flex: 1;" onsubmit="return confirm('Are you sure you want to submit this course structure to CDC?');">
                                    @csrf
                                    <button type="submit" class="btn btn-success" style="width: 100%;">Submit to CDC</button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('hod.courses.show', $department) }}" class="btn btn-secondary" style="flex: 1;">View Designed Courses</a>
                            @if($department->cdc_review_status === 'codes_assigned')
                                <a href="{{ route('hod.faculty-assignments.edit', $department) }}" class="btn btn-primary" style="flex: 1;">Assign Faculty</a>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h3 style="font-size: 18px; margin-bottom: 16px;">Moderator And Faculty Monitoring</h3>
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px;">
        <div class="card" style="padding: 18px;">
            <h4 style="font-size: 16px; margin-bottom: 12px;">Moderators</h4>
            @if($moderators->isEmpty())
                <p style="color: #6b7280;">No moderator accounts have been created yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($moderators as $moderator)
                            <tr>
                                <td>{{ $moderator->name }}</td>
                                <td>{{ $moderator->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card" style="padding: 18px;">
            <h4 style="font-size: 16px; margin-bottom: 12px;">Faculty Workload</h4>
            @if($facultyUsers->isEmpty())
                <p style="color: #6b7280;">No faculty accounts have been created yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Faculty</th>
                            <th>Assigned Subjects</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facultyUsers as $faculty)
                            <tr>
                                <td>{{ $faculty->name }}</td>
                                <td>{{ $faculty->faculty_courses_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

