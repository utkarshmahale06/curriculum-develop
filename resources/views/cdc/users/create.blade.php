@extends('layouts.app')

@section('title', 'Create Account')

@section('content')
<div class="card" style="max-width: 640px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 20px;">
        <div>
            <h2 style="margin-bottom: 6px;">Create Account</h2>
            <p style="color: #6b7280;">Create HOD, moderator, or faculty accounts for the CDC -> HOD -> Moderator -> Faculty flow.</p>
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
                    <option value="hod" {{ old('role') === 'hod' ? 'selected' : '' }}>HOD</option>
                    <option value="moderator" {{ old('role') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                    <option value="faculty" {{ old('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 8 characters" required>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Re-enter password" required>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-success">Create Account</button>
            <a href="{{ route('cdc.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection


