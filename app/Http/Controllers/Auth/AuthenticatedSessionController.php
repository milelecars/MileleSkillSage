<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        Log::debug('Store method invoked.', [
            'email' => $request->email,
            'has_OTP' => $request->has('OTP'),
            'ip_address' => $request->ip(),
        ]);

        // Validate request input
        $validatedData = $request->validate([
            'email' => ['required', 'email', 'exists:admins,email'],
            'OTP' => ['required', 'string'], // Ensure OTP is validated here
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.exists' => 'No account found with the provided email.',
            'OTP.required' => 'OTP is required.',
        ]);
        
        
        // Retrieve the admin record
        $admin = Admin::where('email', $validatedData['email'])->first();

        if (!$admin) {
            Log::warning('Login attempt failed: admin not found.', [
                'email' => $validatedData['email'],
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }



        // Check OTP
        if (!$admin->otp) {
            Log::warning('Login failed: OTP not set.', [
                'admin_id' => $admin->id,
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withErrors(['OTP' => 'OTP not found. Please generate a new one.']);
        }

        if ($admin->otp_expires_at < now()) {
            Log::warning('Login failed: OTP expired.', [
                'admin_id' => $admin->id,
                'otp' => $validatedData['OTP'],
                'otp_expires_at' => $admin->otp_expires_at,
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withErrors(['OTP' => 'The OTP has expired. Please request a new one.']);
        }

        if ($admin->otp !== $validatedData['OTP']) {
            Log::warning('Login failed: OTP mismatch.', [
                'admin_id' => $admin->id,
                'submitted_otp' => $validatedData['OTP'],
                'otp_in_db' => $admin->otp,
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withErrors(['OTP' => 'The OTP entered is incorrect.']);
        }


        $admin->otp = null;
        $admin->otp_expires_at = null;
        $admin->save();
        session()->forget(['otp', 'otp_email', 'otp_expires_at']);


        Auth::guard('web')->login($admin);
        $request->session()->regenerate();
        Log::info('Admin logged in successfully.', [
            'admin_id' => $admin->id,
            'email' => $admin->email,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }


    public function destroy(Request $request)
    {
        if (Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();
            Log::info('Admin logged out.', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'ip_address' => $request->ip(),
            ]);
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}
