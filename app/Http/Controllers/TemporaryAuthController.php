<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TemporaryAuthController extends Controller
{
    public function sendToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(32);
        $expiresAt = now()->addHours(24);

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

        $user = User::firstOrCreate(
            ['email' => $temporaryToken->email],
            ['name' => 'Candidate', 'password' => bcrypt(Str::random()), 'role' => 'candidate']
        );

        Auth::login($user);

        $temporaryToken->delete();

        return redirect()->route('candidate.dashboard');
    }
}
