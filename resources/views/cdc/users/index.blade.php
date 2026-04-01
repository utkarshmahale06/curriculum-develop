@extends('layouts.app')

@section('title', 'Manage Accounts')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h2 style="margin-bottom: 6px;">Manage Accounts</h2>
            <p style="color: #6b7280;">CDC creates and controls department, HOD, and faculty accounts.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('cdc.users.create') }}" class="btn btn-primary">Create Account</a>
            <a href="{{ route('cdc.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <form method="GET" action="{{ route('cdc.users.index') }}" style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 20px;">
        <div class="form-group" style="margin-bottom: 0; min-width: 220px;">
            <label for="role">Filter By Role</label>
            <select id="role" name="role">
                <option value="">All roles</option>
                <option value="department" {{ $selectedRole === 'department' ? 'selected' : '' }}>Department</option>
                <option value="hod" {{ $selectedRole === 'hod' ? 'selected' : '' }}>HOD</option>
                <option value="faculty" {{ $selectedRole === 'faculty' ? 'selected' : '' }}>Faculty</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Apply Filter</button>
    </form>

    @if($users->isEmpty())
        <p style="color: #6b7280; text-align: center; padding: 24px 0;">No accounts found for the selected filter.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Programme Link</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role ?? 'Unassigned') }}</td>
                        <td>{{ $user->linkedDepartment?->name ?? '-' }}</td>
                        <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
