<?php

namespace App\Http\Controllers\Cdc;

use App\Http\Controllers\Controller;
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
            ->when(in_array($role, ['hod', 'moderator', 'faculty'], true), fn ($query) => $query->where('role', $role))
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
        return view('cdc.users.create');
    }

    /**
     * Store a CDC-created account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:hod,moderator,faculty'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('cdc.users.index')
            ->with('success', ucfirst($user->role) . ' account created successfully.');
    }
}
