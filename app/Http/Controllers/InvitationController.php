<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    public function validateEmail(Request $request, $invitationLink)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $invitation = TestInvitation::where('invitation_link', $invitationLink)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return response()->json(['error' => 'Invalid or expired invitation link'], 400);
        }

        $emailList = json_decode($invitation->email_list, true);

        if (!in_array($request->email, $emailList)) {
            return response()->json(['error' => 'Email not found for this invitation'], 400);
        }

        // Email is valid, create a session for the candidate
        session([
            'invitation_link' => $invitationLink,
            'candidate_email' => $request->email
        ]);

        return response()->json([
            'success' => true, 
            'redirect' => route('candidate.dashboard')
        ]);
    }
}