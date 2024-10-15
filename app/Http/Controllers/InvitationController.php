<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use App\Models\Candidate;
use App\Http\Requests\Auth\ValidateInvitationEmailRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;

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

        // Check if the candidate already exists
        $existingCandidate = Candidate::where('email', $validatedData['email'])->first();
        if ($existingCandidate) {
            return back()->withErrors(['email' => 'This email is already registered.']);
        }

        // Create a new candidate in the database
        $candidate = Candidate::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
        ]);

        // Log in the candidate
        Auth::login($candidate); // Start an authorized session

        // Redirect to the candidate dashboard with invitation details
        return redirect()->route('candidate.dashboard', [
            'invitationLink' => $invitationLink,
            'invitationToken' => $invitationToken,
            'testProps' => $invitation->test_id // or any other associated properties you need
        ])->with('success', 'Email validated and candidate created successfully.');
    }

    public function expired()
    {
        return view('invitation.expired');
    }
}