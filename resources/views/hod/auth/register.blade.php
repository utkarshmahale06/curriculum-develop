@extends('layouts.app')

@section('title', 'HOD Signup')

@section('content')
<div style="max-width: 560px; margin: 50px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 12px;">HOD Registration</h2>
        <p style="text-align: center; color: #6b7280; margin-bottom: 22px;">Create the HOD account and link it to a programme/department.</p>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('hod.register.submit') }}">
            @csrf

            <div class="form-group">
                <label for="name">HOD Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter HOD name" required autofocus>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="hod@example.com" required>
            </div>

            <div class="form-group">
                <label for="department_id">Programme / Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select programme</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (int) old('department_id') === $department->id ? 'selected' : '' }}>
                            {{ $department->name }} ({{ $department->code }})
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

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-success" style="flex: 1; padding: 12px;">Create HOD Account</button>
                <a href="{{ route('hod.login') }}" class="btn btn-secondary">Back to Login</a>
            </div>
        </form>
    </div>
</div>
@endsection
