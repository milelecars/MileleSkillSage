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
    
        try {
            // Get active tests (started/completed tests)
            $activeTests = $candidate->tests()
                ->with(['questions'])
                ->select('tests.*', 'candidate_test.status', 'candidate_test.started_at', 
                        'candidate_test.completed_at', 'candidate_test.score')
                ->get()
                ->map(function ($test) {
                    return [
                        'title' => $test->title,
                        'test_id' => $test->id,
                        'status' => $test->pivot->status,
                        'started_at' => $test->pivot->started_at,
                        'completed_at' => $test->pivot->completed_at,
                        'score' => $test->pivot->score,
                        'questions_count' => $test->questions->count(),
                        'has_started' => true
                    ];
                });
    
            // Get tests where the candidate is invited but hasn't started
            $takenTestIds = $candidate->tests()->pluck('tests.id')->toArray();
            
            $invitedTests = Invitation::whereJsonLength('invited_emails', '>', 0)
                ->with('test:id,title,description,duration')
                ->whereJsonContains('invited_emails', $candidate->email)
                ->whereNotIn('test_id', $takenTestIds)
                ->get()
                ->map(function ($invitation) {
                    return [
                        'title' => $invitation->test->title,
                        'test_id' => $invitation->test->id,
                        'status' => 'not_started',
                        'started_at' => null,
                        'completed_at' => null,
                        'score' => null,
                        'questions_count' => $invitation->test->questions()->count(),
                        'has_started' => false
                    ];
                });
    
            // Combine and sort all tests
            $candidateTests = $activeTests->concat($invitedTests)
                ->sortBy(function ($test) {
                    $sortOrder = [
                        'in_progress' => 1,
                        'not_started' => 2,
                        'completed' => 3,
                        'accepted' => 4,
                        'rejected' => 5
                    ];
                    return $sortOrder[$test['status']] ?? 6;
                });
    
            $invitation = $this->validateSession();
            
            return view('candidate.dashboard', compact('candidateTests', 'invitation'));
        } catch (\Exception $e) {
            \Log::error('Error in dashboard:', ['error' => $e->getMessage()]);
            return view('candidate.dashboard', [
                'candidateTests' => collect([]), 
                'invitation' => null
            ])->withErrors(['message' => 'Error loading tests. Please try again.']);
        }
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