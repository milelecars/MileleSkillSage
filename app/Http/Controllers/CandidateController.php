<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    public function dashboard()
    {
        $candidate = Auth::guard('candidate')->user();
        if (!$candidate) {
            return redirect()->route('invitation.candidate-auth');
        }

        $test = session('test');
        if (!$test) {
            // If test is not in session, try to get it from the session test_id
            $testId = session('current_test_id');
            $test = $testId ? Test::find($testId) : null;
        }

        // Get the test status
        $testStatus = null;
        if ($test) {
            $testStatus = $candidate->tests()
                ->where('test_id', $test->id)
                ->first();
        }

        // Get the invitation
        $invitation = $this->validateSession();

        return view('candidate.dashboard', compact('test', 'testStatus', 'invitation'));
    }

    private function validateSession()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');

        if (!$invitationLink || !$candidateEmail) {
            return null;
        }

        return TestInvitation::where('invitation_link', $invitationLink)
            ->where('expiration_date', '>', now())
            ->first();
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