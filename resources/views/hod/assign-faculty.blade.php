@extends('layouts.app')

@section('title', 'Assign Faculty')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Assign Faculty To Subjects</h2>
            <p style="color: #6b7280;">Select faculty accounts for the courses in <strong>{{ $department->name }}</strong>.</p>
        </div>
        <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    @if($facultyUsers->isEmpty())
        <div class="alert alert-warning">
            No faculty accounts are linked to this programme yet. Create faculty accounts first.
        </div>
    @endif

    @if($department->courses->isEmpty())
        <div class="alert alert-warning">
            No course rows are available yet. Faculty can be assigned after the department designs courses.
        </div>
    @else
        <form method="POST" action="{{ route('hod.faculty-assignments.update', $department) }}">
            @csrf

            <table>
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Sr No</th>
                        <th>Course Title</th>
                        <th>Basket</th>
                        <th>Faculty Account</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->courses->sortBy(['semester_name', 'sr_no']) as $course)
                        <tr>
                            <td>{{ $course->semester_name }}</td>
                            <td>{{ $course->sr_no }}</td>
                            <td>{{ $course->course_title }}</td>
                            <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                            <td>
                                <select name="faculty_assignments[{{ $course->id }}]">
                                    <option value="">Not assigned</option>
                                    @foreach($facultyUsers as $faculty)
                                        <option value="{{ $faculty->id }}" {{ (int) old('faculty_assignments.' . $course->id, $course->faculty_user_id) === $faculty->id ? 'selected' : '' }}>
                                            {{ $faculty->name }} ({{ $faculty->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-success">Save Faculty Assignments</button>
                <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
