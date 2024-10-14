<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvitationController extends Controller
{
    public function show($invitationLink)
    {
        // Fetch the invitation based on the invitation link
        $invitation = TestInvitation::where('invitation_link', $invitationLink)
            ->where('expires_at', '>', now())
            ->first(); // Use first() to get the first match

        if (!$invitation) {
            return redirect()->route('invitation.expired')->with('error', 'Invitation not found or expired.');
        }

        // Return a view with the invitation details (adjust as necessary)
        return view('invitation.show', compact('invitation'));
    }
 
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

        // Instead of returning a JSON response, redirect the user to the candidate dashboard
        return redirect()->route('candidate.dashboard')
            ->with('success', 'Email validated successfully.');
    }
    public function expired()
    {
        return view('invitation.expired'); // Create this view to inform the user
    }


}