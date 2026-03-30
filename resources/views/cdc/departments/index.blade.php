@extends('layouts.app')

@section('title', 'Programmes')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin-bottom: 0;">Programmes</h2>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('cdc.departments.create') }}" class="btn btn-primary">Create Programme</a>
            <a href="{{ route('cdc.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if($departments->isEmpty())
        <p style="color: #6b7280; text-align: center; padding: 30px 0;">No programmes found. Create one to get started.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Program Name</th>
                    <th>Code</th>
                    <th>Year</th>
                    <th>Baskets</th>
                    <th>Assigned To</th>
                    <th>Course Workflow</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->code }}</td>
                        <td>{{ $department->year }}</td>
                        <td>{{ $department->courseBaskets->count() }}</td>
                        <td>{{ $department->assignedUser?->name ?? 'Not assigned' }}</td>
                        <td>
                            @if($department->hasAssignedCourseCodes())
                                Codes allocated
                            @elseif($department->hasSubmittedCoursesToCdc())
                                Awaiting CDC codes
                            @elseif($department->courses->count() > 0)
                                Draft designed
                            @else
                                Not started
                            @endif
                        </td>
                        <td>{{ $department->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('cdc.departments.show', $department) }}" class="btn btn-secondary" style="padding: 7px 14px;">View</a>
                                <a href="{{ route('cdc.departments.assign', $department) }}" class="btn btn-primary" style="padding: 7px 14px;">
                                    {{ $department->assigned_user_id ? 'Reassign' : 'Assign' }}
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
