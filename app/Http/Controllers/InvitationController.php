<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Candidate;
use Illuminate\Http\Request;
use App\Models\TestInvitation;
use App\Services\PHPMailerService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Services\EmailTemplateService;
use App\Http\Requests\Auth\ValidateInvitationEmailRequest;

class InvitationController extends Controller
{
    public function show(Request $request, $invitationLink)
    {
        $fullUrl = URL::route('invitation.show', ['invitationLink' => $invitationLink]);
        $invitation = TestInvitation::where('invitation_link', $fullUrl)->firstOrFail();

        if ($invitation->isExpired()) {
            return redirect()->route('invitation.expired');
        }
        if (Auth::guard('candidate')->check()) {
            // If the candidate is authenticated, show the invitation details
            return view('candidate.dashboard', compact('invitation'));
        }
    

        return view('invitation.candidate-auth', [
            'invitation' => $invitation,
            'invitation_token' => $invitation->invitation_token,
        ]);
    }

    public function validateEmail(Request $request, $invitationLink)
    {
        // Step 1: Validate input data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
        $invitationToken = $request->input('invitation_token');

        // Step 2: Retrieve the invitation using the token
        $invitation = TestInvitation::where('invitation_token', $invitationToken)->firstOrFail();

        // Use the email_list directly
        $emailList = $invitation->email_list;

        // Step 3: Check if the email exists in the invitation's email list
        if (!in_array($validatedData['email'], $emailList)) {
            return back()->withErrors(['email' => 'The email does not match the invitation.']);
        }

        // Step 4: Check if the candidate already exists
        $candidate = Candidate::where('email', $validatedData['email'])->first();

        // Step 5: If the candidate exists, log them in
        if ($candidate) {
            Auth::guard('candidate')->login($candidate);
        } else {
            // Step 6: Create a new candidate if they don't exist
            $candidate = Candidate::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);
            // Log in the newly created candidate
            Auth::guard('candidate')->login($candidate);
        }

        // Step 7: Set session data for both new and existing candidates
        $this->setCandidateSession($candidate, $invitation->test_id);

        // Step 8: Redirect to candidate dashboard
        return redirect()->route('candidate.dashboard');
    }

    private function setCandidateSession($candidate, $testId)
    {
        $test = Test::findOrFail($testId);
        session([
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'current_test_id' => $testId,
            'test' => $test,
            'invitation_link' => request()->url(), // Store the current URL as the invitation link
        ]);
    }

    public function expired()
    {
        return view('invitation.expired');
    }

}