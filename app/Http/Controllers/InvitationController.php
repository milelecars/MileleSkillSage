<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Candidate;
use Illuminate\Http\Request;
use App\Models\TestInvitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function show($invitationLink)
    {
        $fullUrl = url('invitation/' . $invitationLink);
        $invitation = TestInvitation::with('test')
            ->where('invitation_link', $fullUrl)
            ->firstOrFail();
        
        Log::info("invitation", [$invitation]);
        
        session([
            'invitation_token' => $invitation->invitation_token,
            'invitation_link' => $fullUrl,
            'test_id' => $invitation->test_id,
        ]);
        
        // Check for expired invitation first
        if ($invitation->expires_at && now()->greaterThan($invitation->expires_at)) {
            return redirect()->route('invitation.expired');
        }
        
        if (Auth::guard('candidate')->check()) {
            $candidate = Auth::guard('candidate')->user();
            $testStatus = $candidate->tests()
                ->where('test_id', $invitation->test_id)
                ->first();
            
            // Check if test is already completed
            if ($testStatus && $testStatus->pivot->completed_at) {
                return redirect()->route('candidate.test.completed');
            }
            
            if ($testStatus) {
                $this->setCandidateSession($candidate, $invitation->test_id);
            }
            
            return view('candidate.dashboard', [
                'invitation' => $invitation,
                'test' => $invitation->test,
                'testStatus' => $testStatus,
            ]);
        }
        
        return view('invitation.candidate-auth', [
            'invitation' => $invitation, 
            'test' => $invitation->test,
            'invitation_token' => $invitation->invitation_token,
        ]);
    }

    public function validateEmail(Request $request, $invitationLink)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);
    
        $invitation = TestInvitation::with('test')
            ->where('invitation_link', url('invitation/' . $invitationLink))
            ->firstOrFail();
            
        // Check for expired invitation
        if ($invitation->expires_at && now()->greaterThan($invitation->expires_at)) {
            return redirect()->route('invitation.expired');
        }
    
        if (!in_array($validatedData['email'], $invitation->email_list)) {
            return back()->withErrors(['email' => 'The email does not match the invitation.']);
        }
    
        // Get or create candidate
        $candidate = Candidate::firstOrCreate(
            ['email' => $validatedData['email']],
            ['name' => $validatedData['name']]
        );
        
        // Check if candidate has already completed this test
        $existingTest = $candidate->tests()
            ->where('test_id', $invitation->test_id)
            ->first();
            
        if ($existingTest && $existingTest->pivot->completed_at) {
            return redirect()->route('candidate.test.completed');
        }
    
        Auth::guard('candidate')->login($candidate);
        $this->setCandidateSession($candidate, $invitation->test_id);
    
        if (!$existingTest) {
            $candidate->tests()->attach($invitation->test_id, [
                'created_at' => now()
            ]);
        }
    
        return redirect()->route('candidate.dashboard');
    }

    private function setCandidateSession($candidate, $testId)
    {
        $test = Test::with('invitation')->findOrFail($testId);
        $testStatus = $candidate->tests()
            ->where('test_id', $testId)
            ->first();

        $sessionData = [
            'candidate_name' => $candidate->name,
            'candidate_email' => $candidate->email,
            'current_test_id' => $testId,
            'test' => $test,
            'invitation_link' => session('invitation_link'),
            'test_session' => null
        ];

        if ($testStatus && $testStatus->pivot->started_at) {
            $sessionData['test_session'] = [
                'test_id' => $testId,
                'start_time' => $testStatus->pivot->started_at,
                'current_question' => session('test_session.current_question', 0),
                'answers' => session('test_session.answers', []),
                'score' => $testStatus->pivot->score
            ];
        }

        session($sessionData);
    }

    public function expired()
    {
        return view('invitation.expired');
    }
}