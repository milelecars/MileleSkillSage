<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TemporaryToken;
use Illuminate\Support\Str;

class TemporaryAuthController extends Controller
{
    public function sendToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(32);
        $expiresAt = now()->addDays(7);

        TemporaryToken::create([
            'email' => $request->email,
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        // Here, you would typically send an email with the login link
        // For now, we'll just return the token in the response
        return response()->json(['token' => $token]);
    }

    public function login(Request $request, $token)
    {
        $temporaryToken = TemporaryToken::where('token', $token)->first();

        if (!$temporaryToken || !$temporaryToken->isValid()) {
            return redirect()->route('login')->withErrors(['token' => 'Invalid or expired token']);
        }

        // Since only admins exist in the users table, we won't create a user for candidates.
        // Instead, you may want to handle the candidate's session here or redirect them to the candidate dashboard directly.
        
        // Here you can handle candidate login logic, for example:
        // session(['candidate_email' => $temporaryToken->email]);

        // After setting the candidate session, redirect to the candidate dashboard.
        return redirect()->route('candidate.dashboard')->with('success', 'Login successful.');
    }
}
