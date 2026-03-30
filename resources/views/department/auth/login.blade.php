@extends('layouts.app')

@section('title', 'Department Login')

@section('content')
<div style="max-width: 420px; margin: 60px auto;">
    <div class="card">
        <h2 style="text-align: center; margin-bottom: 12px;">Department Login</h2>
        <p style="text-align: center; color: #6b7280; margin-bottom: 22px;">Use your department account to access assigned schemes.</p>

        @if($errors->any())
            <div class="alert alert-error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('department.login.submit') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="department@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Department Login</button>
        </form>

        <div style="margin-top: 18px; text-align: center;">
            <a href="{{ route('department.register') }}" class="btn btn-secondary">Create Department Account</a>
        </div>
    </div>
</div>
@endsection
