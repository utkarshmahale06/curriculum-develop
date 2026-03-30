@extends('layouts.app')

@section('title', 'Allocate Course Codes')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 22px;">
        <div>
            <h2 style="margin-bottom: 6px;">Allocate Course Codes</h2>
            <p style="color: #6b7280;">Assign CDC course codes for the submitted courses of <strong>{{ $department->name }}</strong>.</p>
        </div>
        <a href="{{ route('cdc.departments.show', $department) }}" class="btn btn-secondary">Back to Scheme</a>
    </div>

    <div class="alert alert-warning">
        Department submitted these courses on {{ $department->courses_submitted_to_cdc_at?->format('d M Y, h:i A') }}. Enter a unique course code for each row and save.
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('cdc.departments.course-codes.update', $department) }}">
        @csrf

        <div style="overflow-x: auto; border-radius: 8px;">
            <table>
                <thead>
                    <tr>
                        <th>Semester</th>
                        <th>Sr No</th>
                        <th>Basket</th>
                        <th>Course Title</th>
                        <th>Abbrev.</th>
                        <th>Course Type</th>
                        <th>Credits</th>
                        <th>Total Marks</th>
                        <th>Course Code</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->courses->sortBy(['semester_name', 'sr_no']) as $course)
                        <tr>
                            <td>{{ $course->semester_name }}</td>
                            <td>{{ $course->sr_no }}</td>
                            <td>{{ $course->courseBasket?->basket_name ?? '-' }}</td>
                            <td>{{ $course->course_title }}</td>
                            <td>{{ $course->abbreviation }}</td>
                            <td>{{ $course->course_type }}</td>
                            <td>{{ $course->credits }}</td>
                            <td>{{ $course->total_marks }}</td>
                            <td style="min-width: 180px;">
                                <input
                                    type="text"
                                    name="course_codes[{{ $course->id }}]"
                                    value="{{ old('course_codes.' . $course->id, $course->course_code) }}"
                                    placeholder="Enter course code"
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 24px;">
            <button type="submit" class="btn btn-success">Save Course Codes</button>
            <a href="{{ route('cdc.departments.show', $department) }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
