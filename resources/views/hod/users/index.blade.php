@extends('layouts.app')

@section('title', 'Manage Moderator And Faculty Accounts')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h2 style="margin-bottom: 6px;">Manage Moderator And Faculty Accounts</h2>
            <p style="color: #6b7280;">Create and monitor accounts for the programmes assigned to you.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('hod.users.create') }}" class="btn btn-primary">Create Account</a>
            <a href="{{ route('hod.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if($assignedDepartments->isEmpty())
        <div class="alert alert-warning">
            No programmes are assigned to your HOD account yet. CDC must assign a programme before you can create moderator or faculty accounts.
        </div>
    @endif

    <form method="GET" action="{{ route('hod.users.index') }}" style="display: flex; gap: 10px; align-items: end; flex-wrap: wrap; margin-bottom: 20px;">
        <div class="form-group" style="margin-bottom: 0; min-width: 220px;">
            <label for="role">Filter By Role</label>
            <select id="role" name="role">
                <option value="">All roles</option>
                <option value="moderator" {{ $selectedRole === 'moderator' ? 'selected' : '' }}>Moderator</option>
                <option value="faculty" {{ $selectedRole === 'faculty' ? 'selected' : '' }}>Faculty</option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary">Apply Filter</button>
    </form>

    @if($users->isEmpty())
        <p style="color: #6b7280; text-align: center; padding: 24px 0;">No moderator or faculty accounts found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Programme</th>
                    <th>Assigned Subjects</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role ?? 'Unassigned') }}</td>
                        <td>{{ $user->department?->name ?? '-' }}</td>
                        <td>{{ $user->role === 'faculty' ? $user->faculty_courses_count : '-' }}</td>
                        <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
