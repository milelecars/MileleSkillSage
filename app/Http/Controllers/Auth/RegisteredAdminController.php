<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisteredAdminController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'regex:/^[a-zA-Z0-9._%+-]+@milele\.com$/'],
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/'
            ],
        ], [
            'email.regex' => 'The email must be a valid @milele.com address.',
            'password.regex' => 'Password must contain at least one letter, one number, and one special character.',
        ]);


        // Log the registration attempt
        Log::info('Attempting admin registration', [
            'email' => $request->email,
            'name' => $request->name
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Verify the created admin
        Log::info('Admin created', [
            'admin_id' => $admin->id,
            'password_hash' => $admin->password,
            'verification' => Hash::check($request->password, $admin->password) ? 'password hash verified' : 'password hash mismatch'
        ]);

        event(new Registered($admin));

        // Try to log in immediately
        Auth::guard('web')->login($admin);

        // Verify login status
        Log::info('Login status after registration', [
            'is_logged_in' => Auth::check(),
            'logged_in_admin_id' => Auth::id(),
            'guard' => config('auth.defaults.guard')
        ]);

        return redirect()->route('admin.dashboard');
    }
}