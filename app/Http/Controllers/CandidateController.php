<?php

namespace App\Http\Controllers;

use App\Models\TestInvitation;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function dashboard()
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

        return view('candidate.dashboard', [
            'invitation' => $invitation,
            'candidateEmail' => $candidateEmail
        ]);
    }

    public function startTest()
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

        // Logic to start the test
        return view('candidate.test', [
            'test' => $invitation->test,
            'candidateEmail' => $candidateEmail
        ]);
    }
}