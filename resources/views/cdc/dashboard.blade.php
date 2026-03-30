@extends('layouts.app')

@section('title', 'CDC Dashboard')

@section('content')
<div class="card">
    <h2>CDC Dashboard</h2>
    <p style="color: #6b7280; margin-bottom: 25px;">Welcome, {{ Auth::user()->name }}! Manage your programmes from here.</p>

    <div style="display: flex; gap: 15px;">
        <a href="{{ route('cdc.departments.create') }}" class="btn btn-primary">Create Programme</a>
        <a href="{{ route('cdc.departments.index') }}" class="btn btn-success">View Programmes</a>
        <a href="{{ route('department.login') }}" class="btn btn-secondary">Department Portal</a>
    </div>
</div>
@endsection
