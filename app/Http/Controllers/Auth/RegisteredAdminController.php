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

    ], [
        'email.regex' => 'The email must be a valid @milele.com address.',
    ]);

    // Log the registration attempt
    Log::info('Attempting admin registration', [
        'email' => $request->email,
        'name' => $request->name
    ]);

    $admin = Admin::create([
        'name' => $request->name,
        'email' => $request->email,
    ]);

    // Verify the created admin
    Log::info('Admin created', [
        'admin_id' => $admin->id,
    ]);

    event(new Registered($admin));

    // Redirect to the login page with a success message
    return redirect()->route('login')->with('success', 'Registration successful. Please log in to continue.');
}

}