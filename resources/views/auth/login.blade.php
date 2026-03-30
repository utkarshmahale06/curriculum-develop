@extends('layouts.app')

@section('title', 'Login - CDC Management System')

@section('content')
<div style="max-width: 420px; margin: 60px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 25px;">Login</h2>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Login</button>
        </form>

        <div style="margin-top: 18px; padding-top: 18px; border-top: 1px solid #e5e7eb; text-align: center;">
            <p style="color: #6b7280; font-size: 14px; margin-bottom: 10px;">Department users can use their own login portal.</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <a href="{{ route('department.login') }}" class="btn btn-secondary">Department Login</a>
                <a href="{{ route('department.register') }}" class="btn btn-success">First Login Signup</a>
            </div>
        </div>
    </div>
</div>
@endsection
