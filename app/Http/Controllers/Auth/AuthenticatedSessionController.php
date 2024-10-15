<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login with 'admin' guard (users table)
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect to admin dashboard
            return redirect()->intended(route('admin.dashboard'));
        }

        // Attempt login with 'candidate' guard (candidates table)
        if (Auth::guard('candidate')->attempt($credentials)) {
            $request->session()->regenerate();
            $request->session()->put('candidate_id', Auth::guard('candidate')->id());
            return redirect()->intended(route('candidate.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function destroy(Request $request)
    {
        // Logout from the active guard
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        } elseif (Auth::guard('candidate')->check()) {
            Auth::guard('candidate')->logout();
        }

        // Invalidate session and regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}