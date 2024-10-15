<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use App\Http\Requests\Auth\ValidateInvitationEmailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class InvitationController extends Controller
{
    public function show(Request $request, $invitationLink)
    {
        $fullUrl = URL::route('invitation.show', ['invitationLink' => $invitationLink]);
        $invitation = TestInvitation::where('invitation_link', $fullUrl)->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('invitation.expired');
        }

        return view('invitation.candidate-auth', [
            'invitation' => $invitation,
            'invitation_token' => $invitation->invitation_token,
        ]);
    }

    public function validateEmail(Request $request, $invitationLink)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
    
        $invitationToken = $request->input('invitation_token');
    
        // Retrieve the invitation using the token
        $invitation = TestInvitation::where('invitation_token', $invitationToken)->firstOrFail();
    
        // Use the email_list directly (no need to decode)
        $emailList = $invitation->email_list; 
    
        // Check if the email exists in the email list
        if (!in_array($validatedData['email'], $emailList)) {
            return back()->withErrors(['email' => 'The email does not match the invitation.']);
        }
    
        // Redirect to the candidate dashboard with invitation details
        return redirect()->route('candidate.dashboard', [
            'invitationLink' => $invitationLink,
            'invitationToken' => $invitationToken,
            'testProps' => $invitation->test_id // or any other associated properties you need
        ])->with('success', 'Email validated successfully.');
    }
    
    

    public function expired()
    {
        return view('invitation.expired');
    }
}