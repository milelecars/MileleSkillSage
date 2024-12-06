<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Candidate;
use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function show($token)
    {
        $invitation = Invitation::with('test')
            ->where('invitation_token', $token)
            ->firstOrFail();
    
        session([
            'invitation_token' => $invitation->invitation_token,
            'test_id' => $invitation->test_id,
            'test' => $invitation->test, 
        ]);
    
        if (now()->greaterThan($invitation->expiration_date)) {
            return redirect()->route('invitation.expired');
        }
    
        if (Auth::guard('candidate')->check()) {
            $candidate = Auth::guard('candidate')->user();
            $testAttempt = $candidate->tests()
                ->where('test_id', $invitation->test_id)
                ->first();
    
    
            if ($testAttempt) {
                $this->setCandidateSession($candidate, $invitation->test_id);
            }
    
            return view('candidate.dashboard', [
                'invitation' => $invitation,
                'test' => $invitation->test,
                'testAttempt' => $testAttempt,
            ]);
        }
    
        return view('invitation.candidate-auth', [
            'invitation' => $invitation,
            'test' => $invitation->test,
        ]);
    }
    

   public function validateEmail(Request $request, $token)
   {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ]);

        $invitation = Invitation::with('test')
            ->where('invitation_token', $token)
            ->firstOrFail();
            
        if (now()->greaterThan($invitation->expiration_date)) {
            return redirect()->route('invitation.expired');
        }

        $invitedEmails = $invitation->invited_emails;
        if (!in_array($validatedData['email'], $invitedEmails)) {
            return back()->withErrors(['email' => 'The email does not match the invitation.']);
        }

        $candidate = Candidate::firstOrCreate(
            ['email' => $validatedData['email']],
            ['name' => $validatedData['name']]
        );
        
        $existingAttempt = $candidate->tests()->where('test_id', $invitation->test_id)->first();

        if (!$existingAttempt) {
            $candidate->tests()->attach($invitation->test_id, [
                'status' => 'in progress'  
            ]);
        }
        
        session([
            'invitation_token' => $token,
            'test_id' => $invitation->test_id,
            'test' => $invitation->test,
        ]);

        Auth::guard('candidate')->login($candidate);
        
        return redirect()->route('candidate.dashboard');
   }

   private function setCandidateSession($candidate, $testId)
   {
       $test = Test::findOrFail($testId);
       $testAttempt = $candidate->tests()
           ->where('test_id', $testId)
           ->first();

       $sessionData = [
           'candidate_id' => $candidate->id,
           'test_id' => $testId,
       ];

       if ($testAttempt && $testAttempt->pivot->started_at) {
           $sessionData['test_session'] = [
               'test_id' => $testId,
               'start_time' => $testAttempt->pivot->started_at,
               'current_question' => session('test_session.current_question', 0),
               'answers' => session('test_session.answers', []),
           ];
       }

       session($sessionData);
   }

   public function expired()
   {
       return view('invitation.expired');
   }
}