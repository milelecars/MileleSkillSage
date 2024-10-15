<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;
use App\Models\TestInvitation;

class CandidateController extends Controller
{
    public function dashboard(Request $request)
    {
        // Retrieve invitation and test details from the session
        $invitation = $request->session()->get('invitation');
        $testId = $request->session()->get('test_id');

        // Optionally, you can retrieve the invitation again from the database if needed
        // $invitation = TestInvitation::findOrFail($testId);

        return view('candidate.dashboard', compact('invitation', 'testId'));
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
