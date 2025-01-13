<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Http\Controllers\OAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Google\Client;
use Google\Service\Gmail;

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

    public function generateOtp(Request $request)
    {
        // Validate email input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return redirect()->back()->withErrors(['email' => 'No admin account found with this email address.']);
        }

         // Generate a 6-digit OTP
        $otp = random_int(100000, 999999);

        // Store OTP and expiration in the database
        $admin->otp = $otp;
        $admin->otp_expires_at = now()->addMinutes(5);
        $admin->save();

       
        $template = file_get_contents(resource_path('views/emails/otp-email-template.blade.php'));

        // Replace both the OTP and admin variables
        $htmlContent = str_replace(
            ['{{ $otp }}', '{{ $admin->name }}'], 
            [$otp, $admin->name],
            $template
        );

        // Use email sending logic
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
            $service = new Gmail($client);

            $message = new \Google\Service\Gmail\Message();
            $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
            $rawMessage .= "To: <{$admin->email}>\r\n";
            $rawMessage .= "Subject: =?utf-8?B?" . base64_encode("Your OTP Code") . "?=\r\n";
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
            $rawMessage .= $htmlContent;

            $message->setRaw(base64_encode($rawMessage));
            $service->users_messages->send('me', $message);

            Log::info('OTP sent successfully', [
                'email' => $admin->email,
                'otp' => $otp,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['email_error' => 'Failed to send OTP. Please try again later.']);
        }

        $request->session()->put('otp_generated', true);
        return redirect()->back()->with('success', 'OTP sent to your email');
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
