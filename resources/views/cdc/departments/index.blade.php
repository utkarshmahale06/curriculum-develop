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

    <form method="GET" action="{{ route('cdc.departments.index') }}" style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 20px;">
        <div class="form-group" style="margin-bottom: 0; min-width: 220px;">
            <label for="status">Filter By Workflow</label>
            <select id="status" name="status">
                <option value="">All programmes</option>
                <option value="not_started" {{ ($status ?? '') === 'not_started' ? 'selected' : '' }}>Not started</option>
                <option value="draft" {{ ($status ?? '') === 'draft' ? 'selected' : '' }}>Draft in progress</option>
                <option value="submitted" {{ ($status ?? '') === 'submitted' ? 'selected' : '' }}>Awaiting CDC review</option>
                <option value="revision_requested" {{ ($status ?? '') === 'revision_requested' ? 'selected' : '' }}>Revision requested</option>
                <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved by CDC</option>
                <option value="codes_assigned" {{ ($status ?? '') === 'codes_assigned' ? 'selected' : '' }}>Codes assigned</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Apply Filter</button>
    </form>

    @if($departments->isEmpty())
        <p style="color: #6b7280; text-align: center; padding: 30px 0;">No programmes found. Create one to get started.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Programme Name</th>
                    <th>Code</th>
                    <th>Year</th>
                    <th>Baskets</th>
                    <th>Department Owner</th>
                    <th>Workflow</th>
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
                            {{ $department->workflowLabel() }}
                        </td>
                        <td>{{ $department->created_at->format('d M Y, h:i A') }}</td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <a href="{{ route('cdc.departments.show', $department) }}" class="btn btn-secondary" style="padding: 7px 14px;">View</a>
                                <a href="{{ route('cdc.departments.assign', $department) }}" class="btn btn-primary" style="padding: 7px 14px;">
                                    {{ $department->assigned_user_id ? 'Reassign Owner' : 'Assign Owner' }}
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
