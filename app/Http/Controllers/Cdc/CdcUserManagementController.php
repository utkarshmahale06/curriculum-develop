<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class CdcUserManagementController extends Controller
{
    /**
     * Show the CDC user management page.
     */
    public function index(Request $request)
    {
        $role = $request->string('role')->toString();

        $users = User::query()
            ->when(in_array($role, ['department', 'hod', 'faculty'], true), fn ($query) => $query->where('role', $role))
            ->with('linkedDepartment')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('cdc.users.index', [
            'users' => $users,
            'selectedRole' => $role,
        ]);
    }

    /**
     * Show the create-user form.
     */
    public function create()
    {
        $programmes = Department::orderBy('name')->get();

        return view('cdc.users.create', compact('programmes'));
    }

    /**
     * Store a CDC-created account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:department,hod,faculty'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (in_array($validated['role'], ['hod', 'faculty'], true) && empty($validated['department_id'])) {
            return back()
                ->withErrors(['department_id' => 'Select a programme for HOD and faculty accounts.'])
                ->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'] ?: null,
            'password' => $validated['password'],
        ]);

        return redirect()->route('cdc.users.index')
            ->with('success', ucfirst($user->role) . ' account created successfully.');
    }
}
