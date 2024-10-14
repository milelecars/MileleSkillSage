<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;
use App\Models\TestInvitation;

class CandidateController extends Controller
{
    public function dashboard(Request $request)
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');
        
        if (!$invitationLink || !$candidateEmail) {
            return redirect()->route('invitation.expired');
        }
    
        $invitation = TestInvitation::where('invitation_link', $invitationLink)
            ->where('expires_at', '>', now())
            ->first();
    
        if (!$invitation) {
            return redirect()->route('invitation.expired');
        }
    
        // Pass the test associated with the invitation to the view
        return view('candidate.dashboard', [
            'test' => $invitation->test,
            'candidateEmail' => $candidateEmail
        ]);
    }
    private function validateSession()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');

        if (!$invitationLink || !$candidateEmail) {
            return false;
        }

        $invitation = TestInvitation::where('invitation_link', $invitationLink)
            ->where('expires_at', '>', now())
            ->first();

        return $invitation;
    }

    public function startTest()
    {
        $invitation = $this->validateSession();

        if (!$invitation) {
            return redirect()->route('invitation.expired');
        }

        return view('candidate.test', [
            'test' => $invitation->test,
            'candidateEmail' => session('candidate_email')
        ]);
    }

    
}