<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HodUserManagementController extends Controller
{
    /**
     * Show moderator and faculty accounts managed by the HOD.
     */
    public function index(Request $request)
    {
        $role = $request->string('role')->toString();
        $assignedDepartments = $this->assignedDepartments();
        $assignedDepartmentIds = $assignedDepartments->pluck('id')->all();

        $users = User::query()
            ->whereIn('role', ['moderator', 'faculty'])
            ->whereIn('department_id', $assignedDepartmentIds)
            ->when(in_array($role, ['moderator', 'faculty'], true), fn ($query) => $query->where('role', $role))
            ->with('department')
            ->withCount('facultyCourses')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('hod.users.index', [
            'users' => $users,
            'selectedRole' => $role,
            'assignedDepartments' => $assignedDepartments,
        ]);
    }

    /**
     * Show the HOD account creation form.
     */
    public function create()
    {
        $assignedDepartments = $this->assignedDepartments();

        return view('hod.users.create', compact('assignedDepartments'));
    }

    /**
     * Store a moderator or faculty account for one of the HOD's programmes.
     */
    public function store(Request $request)
    {
        $assignedDepartments = $this->assignedDepartments();
        $assignedDepartmentIds = $assignedDepartments->pluck('id')->all();

        if ($assignedDepartmentIds === []) {
            return redirect()->route('hod.users.index')
                ->with('error', 'CDC must assign a programme before you can create moderator or faculty accounts.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:moderator,faculty'],
            'department_id' => ['required', 'integer', 'in:' . implode(',', $assignedDepartmentIds)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'department_id.in' => 'Select one of your assigned programmes.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department_id' => $validated['department_id'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('hod.users.index')
            ->with('success', ucfirst($user->role) . ' account created successfully.');
    }

    /**
     * Get programmes assigned to the logged-in HOD.
     */
    protected function assignedDepartments()
    {
        return Auth::user()
            ->assignedDepartments()
            ->orderBy('name')
            ->get();
    }
}
