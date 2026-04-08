@extends('layouts.app')

@section('title', 'Assign Faculty')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Assign Faculty</h2>
            <p style="color: #6b7280;">Select a faculty member for each course in <strong>{{ $department->name }}</strong> ({{ $department->code }}).</p>
        </div>
        <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($facultyUsers->isEmpty())
        <div class="alert alert-warning">
            No faculty accounts available yet. Ask CDC to create faculty accounts first.
        </div>
    @endif

    @if($department->courses->isEmpty())
        <div class="alert alert-warning">
            No courses available. Faculty can be assigned after courses are designed.
        </div>
    @else
        <form method="POST" action="{{ route('hod.faculty-assignments.update', $department) }}">
            @csrf

            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Semester</th>
                        <th>Basket</th>
                        <th>Credits</th>
                        <th>Assign Faculty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->courses->sortBy(['semester_name', 'sr_no']) as $course)
                        <tr>
                            <td style="font-weight: 600;">{{ $course->course_code && ! \Illuminate\Support\Str::startsWith($course->course_code, ['DRAFT-', 'SUBMITTED-', 'PENDING-']) ? $course->course_code : 'Pending' }}</td>
                            <td>{{ $course->course_title }}</td>
                            <td>{{ $course->semester_name }} · Sr {{ $course->sr_no }}</td>
                            <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                            <td>{{ $course->credits }}</td>
                            <td style="min-width: 280px;">
                                <select name="faculty_assignments[{{ $course->id }}]" style="width: 100%;">
                                    <option value="">— Select Faculty —</option>
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
                <button type="submit" class="btn btn-success">Save Assignments</button>
                <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
