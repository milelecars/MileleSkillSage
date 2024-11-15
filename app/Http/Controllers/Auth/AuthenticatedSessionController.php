<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

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

        // First check if admin exists
        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'No admin account found with this email address.');
        }

        // Now attempt authentication
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            
            // Check if admin account is active
            if (!$admin->is_active) {
                Auth::guard('web')->logout();
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Your account is currently inactive. Please contact the super admin.');
            }

            // Log login attempt
            \Log::info('Admin login successful', ['admin_id' => $admin->id, 'email' => $admin->email]);
            
            return redirect()
                ->intended(route('admin.dashboard'))
                ->with('success', 'Welcome back, ' . $admin->name);
        }

        // If the authentication attempt fails
        \Log::warning('Failed admin login attempt', ['email' => $request->email]);
        
        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ]);
    }

    public function destroy(Request $request)
    {
        if (Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();
            \Log::info('Admin logged out', ['admin_id' => $admin->id]);
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')
            ->with('success', 'You have been successfully logged out.');
    }
}