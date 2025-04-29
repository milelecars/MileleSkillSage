<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Candidate;
use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        if (Auth::guard('candidate')->check()) {
            $candidate = Auth::guard('candidate')->user();
            $testAttempt = $candidate->tests()
                ->where('test_id', $invitation->test_id)
                ->first();

            if ($testAttempt) {
                $this->setCandidateSession($candidate, $invitation->test_id);
            }

            
            return redirect()->route('candidate.dashboard');
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

        $invites = $invitation->invited_emails['invites'] ?? [];
        $candidateInvite = collect($invites)->firstWhere('email', $validatedData['email']);
        
        if (!$candidateInvite) {
            return back()->withErrors(['email' => 'The email does not match the invitation.']);
        }
            
        $role = $candidateInvite['role'];
        $candidate = Candidate::firstOrCreate(
            ['email' => $validatedData['email']],
            ['name' => $validatedData['name']]
        );
        
        $existingAttempt = $candidate->tests()->where('test_id', $invitation->test_id)->first();

        if (!$existingAttempt) {
            $candidate->tests()->attach($invitation->test_id, [
                'status' => 'not started',
                'role' =>   $role
            ]);
        }else{
            $candidate->tests()->updateExistingPivot($invitation->test_id, [
                'role' => $role,
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

   public function extendDeadline(Request $request)
   {
       $request->validate([
           'test_id' => 'required|exists:tests,id',
           'email' => 'required|email',
           'new_deadline' => 'required|date|after:now'
       ]);
   
       try {
           DB::beginTransaction();
   
           
           $invitation = DB::table('invitations')
               ->where('test_id', $request->test_id)
               ->first();
   
           if (!$invitation) {
               throw new \Exception('Invitation not found for this test.');
           }
   
           
           $invitedEmails = json_decode($invitation->invited_emails, true);
           $invites = $invitedEmails['invites'] ?? [];
           $updated = false;
   
           
           foreach ($invites as $key => $invite) {
               if ($invite['email'] === $request->email) {
                   $invites[$key]['deadline'] = Carbon::parse($request->new_deadline)->format('Y-m-d H:i:s');
                   $updated = true;
                   break;
               }
           }
   
           if (!$updated) {
               throw new \Exception('Email not found in invitation list.');
           }
   
           
            DB::table('invitations')
               ->where('test_id', $request->test_id)
               ->update([
                   'invited_emails' => json_encode(['invites' => $invites])
                ]);


            $candidate = DB::table('candidates')->where('email', $request->email)->first();
            if (!$candidate) {
                throw new \Exception('Candidate not found.');
            }

            DB::table('candidate_test')
                ->where('candidate_id', $candidate->id)
                ->where('test_id', $request->test_id)
                ->update(['status' => 'not started']);
   
           Log::info('Deadline extended successfully', [
               'test_id' => $request->test_id,
               'email' => $request->email,
               'new_deadline' => $request->new_deadline
           ]);
   
           DB::commit();
           return redirect()->back()->with('success', 'Deadline extended successfully.');
       } catch (\Exception $e) {
           DB::rollBack();
           
           Log::error('Failed to extend deadline', [
               'test_id' => $request->test_id,
               'email' => $request->email,
               'error' => $e->getMessage()
           ]);
   
           return redirect()->back()->with('error', 'Failed to extend deadline. ' . $e->getMessage());
       }
   }
}