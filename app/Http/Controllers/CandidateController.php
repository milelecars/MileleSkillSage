<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class CandidateController extends Controller
{
    public function dashboard()
    {
        $candidate = Auth::guard('candidate')->user();
        if (!$candidate) {
            return redirect()->route('invitation.candidate-auth');
        }

        // Retrieve test from session
        $test = session('test');
        if (!$test) {
            $testId = session('test_id');
            $test = $testId ? Test::find($testId) : null;
        }

        // Validate session and fetch invitation
        $invitation = $this->validateSession();
        
        // If we don't have a test yet, that's okay for first-time users
        if (!$test) {
            return view('candidate.dashboard', [
                'test' => null,
                'testAttempt' => null,
                'invitation' => $invitation
            ]);
        }

        // If we have a test, check for an attempt
        $testAttempt = $candidate->tests()->where('test_id', $test->id)->first();
        
        return view('candidate.dashboard', compact('test', 'testAttempt', 'invitation'));
    }


    private function validateSession()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');

        if (!$invitationLink || !$candidateEmail) {
            return null;
        }

        return Invitation::where('invitation_link', $invitationLink)
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