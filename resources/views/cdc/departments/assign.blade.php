@extends('layouts.app')

@section('title', 'Assign Scheme')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <p style="color: #6b7280;">Assign <strong>{{ $department->name }}</strong> to the HOD account that will design its courses.</p>
        </div>
        <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Back to Programmes</a>
    </div>

    @if($hodUsers->isEmpty())
        <div class="alert alert-warning">
            No HOD accounts exist yet. Create a HOD account from the user management screen first.
        </div>
    @else
        <form method="POST" action="{{ route('cdc.departments.assign.update', $department) }}">
            @csrf

            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Assigned Schemes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hodUsers as $hodUser)
                        <tr>
                            <td style="width: 90px;">
                                <input
                                    type="radio"
                                    name="assigned_user_id"
                                    value="{{ $hodUser->id }}"
                                    {{ (int) old('assigned_user_id', $department->assigned_user_id) === $hodUser->id ? 'checked' : '' }}
                                >
                            </td>
                            <td>{{ $hodUser->name }}</td>
                            <td>{{ $hodUser->email }}</td>
                            <td>{{ $hodUser->assigned_departments_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @error('assigned_user_id')
                <div class="form-error" style="margin-top: 10px;">{{ $message }}</div>
            @enderror

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-success">Save Assignment</button>
                <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
