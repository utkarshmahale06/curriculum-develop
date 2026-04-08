<?php

namespace App\Http\Controllers;

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
     * Show the HOD login form.
     */
    public function showHodLoginForm()
    {
        return view('hod.auth.login');
    }

    /**
     * Redirect legacy HOD signup requests to CDC-managed flow.
     */
    public function showHodRegisterForm()
    {
        return redirect()->route('hod.login')
            ->with('warning', 'HOD accounts are now created by CDC. Please contact CDC for account setup.');
    }

    /**
     * Show the faculty login form.
     */
    public function showFacultyLoginForm()
    {
        return view('faculty.auth.login');
    }

    /**
     * Redirect legacy faculty signup requests to CDC-managed flow.
     */
    public function showFacultyRegisterForm()
    {
        return redirect()->route('faculty.login')
            ->with('warning', 'Faculty accounts are now created by CDC. Please contact CDC for account setup.');
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
