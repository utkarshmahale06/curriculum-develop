@extends('layouts.app')

@section('title', 'Assign Scheme')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 12px;">
        <div>
            <h2 style="margin-bottom: 6px;">Assign Scheme</h2>
            <p style="color: #6b7280;">Assign <strong>{{ $department->name }}</strong> to a department user account.</p>
        </div>
        <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Back to Programmes</a>
    </div>

    @if($departmentUsers->isEmpty())
        <div class="alert alert-warning">
            No department accounts exist yet. Ask the department user to create an account from the department first-login page.
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
                    @foreach($departmentUsers as $departmentUser)
                        <tr>
                            <td style="width: 90px;">
                                <input
                                    type="radio"
                                    name="assigned_user_id"
                                    value="{{ $departmentUser->id }}"
                                    {{ (int) old('assigned_user_id', $department->assigned_user_id) === $departmentUser->id ? 'checked' : '' }}
                                >
                            </td>
                            <td>{{ $departmentUser->name }}</td>
                            <td>{{ $departmentUser->email }}</td>
                            <td>{{ $departmentUser->assigned_departments_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @error('assigned_user_id')
                <div class="form-error" style="margin-top: 10px;">{{ $message }}</div>
            @enderror

            <div style="display: flex; gap: 10px; margin-top: 24px;">
                <button type="submit" class="btn btn-success">Assign Scheme</button>
                <a href="{{ route('cdc.departments.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    @endif
</div>
@endsection
