<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Show the department login form.
     */
    public function showDepartmentLoginForm()
    {
        return view('department.auth.login');
    }

    /**
     * Show the department registration form.
     */
    public function showDepartmentRegisterForm()
    {
        return view('department.auth.register');
    }

    /**
     * Show the HOD login form.
     */
    public function showHodLoginForm()
    {
        return view('hod.auth.login');
    }

    /**
     * Show the HOD registration form.
     */
    public function showHodRegisterForm()
    {
        $departments = Department::orderBy('name')->get();

        return view('hod.auth.register', compact('departments'));
    }

    /**
     * Show the faculty login form.
     */
    public function showFacultyLoginForm()
    {
        return view('faculty.auth.login');
    }

    /**
     * Show the faculty registration form.
     */
    public function showFacultyRegisterForm()
    {
        $departments = Department::orderBy('name')->get();

        return view('faculty.auth.register', compact('departments'));
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isCdc()) {
                return redirect()->intended(route('cdc.dashboard'));
            }

            if (Auth::user()->isDepartment()) {
                return redirect()->intended(route('department.dashboard'));
            }

            if (Auth::user()->isHod()) {
                return redirect()->intended(route('hod.dashboard'));
            }

            if (Auth::user()->isFaculty()) {
                return redirect()->intended(route('faculty.dashboard'));
            }

            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle a department login request.
     */
    public function departmentLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isDepartment()) {
                return redirect()->intended(route('department.dashboard'));
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'This login is only for department users.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Create a department account on first login.
     */
    public function departmentRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'department',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('department.dashboard')
            ->with('success', 'Department account created successfully.');
    }

    /**
     * Handle a HOD login request.
     */
    public function hodLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isHod()) {
                return redirect()->intended(route('hod.dashboard'));
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'This login is only for HOD users.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Create a HOD account.
     */
    public function hodRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'hod',
            'department_id' => $validated['department_id'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('hod.dashboard')
            ->with('success', 'HOD account created successfully.');
    }

    /**
     * Handle a faculty login request.
     */
    public function facultyLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (Auth::user()->isFaculty()) {
                return redirect()->intended(route('faculty.dashboard'));
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'This login is only for faculty users.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Create a faculty account.
     */
    public function facultyRegister(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'faculty',
            'department_id' => $validated['department_id'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('faculty.dashboard')
            ->with('success', 'Faculty account created successfully.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
