<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'OTP' => 'required|string',
        ]);

        // Find the admin by email
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No admin account found with this email address.']);
        }

        // Verify the password
        if (!Hash::check($request->password, $admin->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => 'The provided password is incorrect.']);
        }

        // Verify the OTP
        if (!$admin->otp || $admin->otp_expires_at < now() || $admin->otp !== $request->OTP) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['OTP' => 'Invalid or expired OTP code.']);
        }

        // Clear the OTP after successful verification
        $admin->otp = null;
        $admin->otp_expires_at = null;
        $admin->save();

        // Log the admin in
        Auth::guard('web')->login($admin);
        $request->session()->regenerate();

        Log::info('Admin login successful', [
            'admin_id' => $admin->id,
            'email' => $admin->email,
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        if (Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();
            Log::info('Admin logged out', ['admin_id' => $admin->id]);
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
