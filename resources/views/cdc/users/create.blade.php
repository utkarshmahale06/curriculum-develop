@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="card" style="max-width: 760px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 20px;">
        <div>
            <h2 style="margin-bottom: 6px;">Create Account</h2>
            <p style="color: #6b7280;">Create department, HOD, or faculty accounts from CDC.</p>
        </div>
        <a href="{{ route('cdc.users.index') }}" class="btn btn-secondary">Back to Accounts</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('cdc.users.store') }}">
        @csrf

        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="user@example.com" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="">Select role</option>
                    <option value="department" {{ old('role') === 'department' ? 'selected' : '' }}>Department</option>
                    <option value="hod" {{ old('role') === 'hod' ? 'selected' : '' }}>HOD</option>
                    <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                </select>
            </div>

            <div class="form-group">
                <label for="department_id">Linked Programme</label>
                <select id="department_id" name="department_id">
                    <option value="">Select when required</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" {{ (int) old('department_id') === $programme->id ? 'selected' : '' }}>
                            {{ $programme->name }} ({{ $programme->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required>
            </div>
        </div>

        <div class="alert alert-warning" style="margin-top: 12px;">
            Department accounts can stay unlinked until a programme is assigned. HOD and faculty accounts should be linked to the correct programme.
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-success">Create Account</button>
            <a href="{{ route('cdc.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
