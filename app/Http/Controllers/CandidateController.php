<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Invitation;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class CandidateController extends Controller
{

    private const STATUS_SORT_ORDER = [
        'in_progress' => 10,
        'completed' => 20,
        'accepted' => 30,
        'rejected' => 40,
        'not_started' => 50,
        'expired' => 60
    ];

    public function dashboard()
    {
        $candidate = Auth::guard('candidate')->user();
        \Log::debug('Current candidate:', ['candidate_id' => $candidate->id, 'email' => $candidate->email]);

        if (!$candidate) {
            \Log::warning('No authenticated candidate found');
            return redirect()->route('invitation.candidate-auth');
        }

        try {
            // Get all invitations for this candidate's email
            $allInvitations = Invitation::whereJsonLength('invited_emails->invites', '>', 0)
                ->with(['test:id,title,description,duration', 'test.questions'])
                ->whereJsonContains('invited_emails->invites', ['email' => $candidate->email])
                ->get();

            \Log::debug('Retrieved all invitations:', [
                'count' => $allInvitations->count()
            ]);

            // Fetch all tests (valid and expired)
            $allTests = $allInvitations->flatMap(function ($invitation) use ($candidate) {
                if (!$invitation->test) {
                    \Log::warning('Invitation without valid test found', ['invitation_id' => $invitation->id]);
                    return []; // Skip this invitation
                }
                
                $invites = collect($invitation->invited_emails['invites']);
                $candidateInvites = $invites->where('email', $candidate->email);

                return $candidateInvites->map(function ($invite) use ($invitation, $candidate) {
                    $deadline = Carbon::parse($invite['deadline']);
                    $isExpired = now()->greaterThan($deadline);
                    $questions = Question::where('test_id', $invitation->test_id)->get();
                    $hasMCQ = $questions->contains('question_type', 'MCQ');
                    $hasLSQ = $questions->contains('question_type', 'LSQ');
                    
                    \Log::debug('isExpired:', [
                        $isExpired
                    ]);

                    // Retrieve candidate_test record if exists
                    $candidateTest = DB::table('candidate_test')
                        ->where('candidate_id', $candidate->id)
                        ->where('test_id', $invitation->test_id)
                        ->first();

                    // Default status determination
                    if ($isExpired) {
                        if ($candidateTest) {
                            \Log::debug('Expired test found in candidate_test:', [
                                'candidate_id' => $candidate->id,
                                'test_id' => $invitation->test_id,
                                'existing_status' => $candidateTest->status
                            ]);
                    
                            // If test is expired but has any status other than 'not started', keep that status
                            $status = str_replace(' ', '_', strtolower($candidateTest->status)) === 'not_started' 
                                ? 'expired' 
                                : str_replace(' ', '_', $candidateTest->status);
                            
                            \Log::debug('Setting status for expired test:', [
                                'test_id' => $invitation->test_id,
                                'status' => $status,
                                'original_status' => $candidateTest->status
                            ]);
                        } else {
                            $status = 'expired';
                        }
                    } else {
                        // If not expired, check candidate_test status
                        $status = $candidateTest ? str_replace(' ', '_', $candidateTest->status) : 'not_started';
                    }

                    
                   
                    $testData = [
                        'title' => $invitation->test->title,
                        'test_id' => $invitation->test->id,
                        'status' => $status,
                        'started_at' => $candidateTest ? $candidateTest->started_at : null,
                        'completed_at' => $candidateTest ? $candidateTest->completed_at : null,
                        'score' => $candidateTest ? $candidateTest->score : null,
                        'red_flags' => $candidateTest ? $candidateTest->red_flags : null,
                        'correct_answers' => $candidateTest ? $candidateTest->correct_answers : null,
                        'wrong_answers' => $candidateTest ? $candidateTest->wrong_answers : null,
                        'questions_count' => $invitation->test->questions->count(),
                        'hasMCQ' => $hasMCQ,
                        'hasLSQ' => $hasLSQ,
                        'has_started' => $candidateTest ? true : false
                    ];

                    \Log::debug('Processed test data:', $testData);
                    return $testData;
                    
                });
            });

            // Sort tests by status priority
            $sortedTests = $allTests->sortBy(function ($test) {
                $sortOrder = [
                    'in_progress' => 1,
                    'not_started' => 2,
                    'completed' => 3,
                    'suspended' => 4,
                    'accepted' => 5,
                    'rejected' => 6,
                    'expired' => 7
                ];
                return $sortOrder[$test['status']] ?? 7;
            });

            \Log::debug('Final test list:', [
                'total_count' => $sortedTests->count(),
                'status_breakdown' => $sortedTests->groupBy('status')->map->count()
            ]);

            return view('candidate.dashboard', ['candidateTests' => $sortedTests]);
        } catch (\Exception $e) {
            \Log::error('Error in dashboard:', ['error' => $e->getMessage()]);
            return view('candidate.dashboard', ['candidateTests' => collect([])])
                ->withErrors(['message' => 'Error loading tests. Please try again.']);
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