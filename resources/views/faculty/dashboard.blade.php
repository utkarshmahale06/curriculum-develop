@extends('layouts.app')

@section('title', 'Faculty Dashboard')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Faculty Dashboard</h2>
            <p style="color: #6b7280;">Welcome, {{ Auth::user()->name }}. Review the subjects assigned to you.</p>
        </div>
    </div>

    @if($assignedCourses->isEmpty())
        <div class="alert alert-warning">
            No subjects are assigned to your account yet.
        </div>
    @else
        @if($department)
            <div class="alert alert-success">
                Programme: <strong>{{ $department->name }}</strong> ({{ $department->code }}) | Year: {{ $department->year }}
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Sr No</th>
                    <th>Course Code</th>
                    <th>Course Title</th>
                    <th>Basket</th>
                    <th>Credits</th>
                    <th>Total Marks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedCourses as $course)
                    <tr>
                        <td>{{ $course->semester_name }}</td>
                        <td>{{ $course->sr_no }}</td>
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
