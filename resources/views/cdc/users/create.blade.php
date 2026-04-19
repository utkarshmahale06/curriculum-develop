@extends('layouts.app')

@section('title', 'Create HOD Account')

@section('content')
<div class="card" style="max-width: 640px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 20px;">
        <div>
            <h2 style="margin-bottom: 6px;">Create HOD Account</h2>
            <p style="color: #6b7280;">CDC creates only HOD accounts. HOD will create moderator and faculty accounts.</p>
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
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="hod@example.com" required>
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
            To create moderator or faculty accounts, log in as HOD and use HOD account management.
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-success">Create HOD Account</button>
            <a href="{{ route('cdc.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
