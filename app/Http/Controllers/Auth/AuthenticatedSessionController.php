<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
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
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        Log::info('Login credentials check', [
            'email' => $credentials['email'],
            'provided_password' => $credentials['password'],
            'guard' => Auth::getDefaultDriver(),
            'provider' => config('auth.guards.web.provider')
        ]);

        // Find admin
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'No admin account found with this email address.');
        }

        // Debug password check
        Log::info('Password check details', [
            'provided_password' => $credentials['password'],
            'stored_hash' => $admin->password,
            'hash_check_result' => Hash::check($credentials['password'], $admin->password) ? 'true' : 'false'
        ]);

        
        // Try both direct hash check and Auth attempt
        $hashCheck = Hash::check($credentials['password'], $admin->password);
        $authAttempt = Auth::guard('web')->attempt($credentials);

        Log::info('Authentication attempts', [
            'hash_check' => $hashCheck ? 'passed' : 'failed',
            'auth_attempt' => $authAttempt ? 'passed' : 'failed'
        ]);

        if ($hashCheck) {
            Auth::guard('web')->login($admin);
            $request->session()->regenerate();
            
            Log::info('Admin login successful', [
                'admin_id' => $admin->id, 
                'email' => $admin->email
            ]);
            
            return redirect()
                ->intended(route('admin.dashboard'));
        }

        Log::warning('Failed admin login attempt - password mismatch', [
            'email' => $request->email
        ]);
        
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
    }

    public function destroy(Request $request)
    {
        // Check if user is candidate and store token before logout
        $isCandidate = Auth::guard('candidate')->check();
        $token = $request->session()->get('invitation_token');
        
        if (Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();
            Log::info('Admin logged out', ['admin_id' => $admin->id]);
            Auth::guard('web')->logout();
        } else if (Auth::guard('candidate')->check()) {
            $candidate = Auth::guard('candidate')->user();
            Log::info('Candidate logged out', ['candidate_id' => $candidate->id]);
            Auth::guard('candidate')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Redirect based on user type
        if ($isCandidate && $token) {
            return redirect()->route('invitation.show', ['token' => $token]);
        }
        
        return redirect()->route('welcome');
    }
}