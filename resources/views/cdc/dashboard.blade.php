@extends('layouts.app')

@section('title', 'CDC Dashboard')

@section('content')
<div class="card">
    <h2>CDC Dashboard</h2>
    <p style="color: #6b7280; margin-bottom: 25px;">Welcome, {{ Auth::user()->name }}. Manage programmes, review submissions, and create institutional accounts from here.</p>

    <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Programmes</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $programmeCount }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Pending Assignment</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $pendingAssignmentCount }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Awaiting Review</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $pendingReviewCount }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 13px; color: #6b7280;">Approved, Pending Codes</div>
            <div style="font-size: 24px; font-weight: 600;">{{ $approvedPendingCodesCount }}</div>
        </div>
    </div>

    <div class="alert alert-warning" style="margin-bottom: 24px;">
        Accounts managed by CDC:
        HOD {{ $accountCounts['hod'] }},
        Faculty {{ $accountCounts['faculty'] }}.
    </div>

    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="{{ route('cdc.departments.create') }}" class="btn btn-primary">Create Programme</a>
        <a href="{{ route('cdc.departments.index') }}" class="btn btn-success">View Programmes</a>
        <a href="{{ route('cdc.users.index') }}" class="btn btn-secondary">Manage Accounts</a>
    </div>
</div>
@endsection
