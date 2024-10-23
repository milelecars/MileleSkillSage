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

        
        
        return response()->json(['token' => $token]);
    }

    public function login(Request $request, $token)
    {
        $temporaryToken = TemporaryToken::where('token', $token)->first();

        if (!$temporaryToken || !$temporaryToken->isValid()) {
            return redirect()->route('login')->withErrors(['token' => 'Invalid or expired token']);
        }
        
        return redirect()->route('candidate.dashboard')->with('success', 'Login successful.');
    }
}
