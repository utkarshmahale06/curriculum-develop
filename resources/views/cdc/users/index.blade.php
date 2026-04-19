@extends('layouts.app')

@section('title', 'Manage HOD Accounts')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h2 style="margin-bottom: 6px;">Manage HOD Accounts</h2>
            <p style="color: #6b7280;">CDC creates HOD accounts only. HOD creates moderator and faculty accounts.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('cdc.users.create') }}" class="btn btn-primary">Create HOD Account</a>
            <a href="{{ route('cdc.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if($users->isEmpty())
        <p style="color: #6b7280; text-align: center; padding: 24px 0;">No HOD accounts found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role ?? 'Unassigned') }}</td>
                        <td>{{ $user->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
