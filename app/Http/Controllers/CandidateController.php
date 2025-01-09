<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Invitation;
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
                'count' => $allInvitations->count(),
                'invitations' => $allInvitations->map(function($inv) {
                    return [
                        'test_id' => $inv->test_id,
                        'invited_emails' => $inv->invited_emails
                    ];
                })
            ]);

            // First, collect all non-expired invites for this candidate
            $validInvites = collect();
            foreach ($allInvitations as $invitation) {
                $invites = collect($invitation->invited_emails['invites']);
                $candidateInvites = $invites->where('email', $candidate->email);
                
                \Log::debug('Processing invitation for candidate:', [
                    'test_id' => $invitation->test_id,
                    'candidate_email' => $candidate->email,
                    'matching_invites_found' => $candidateInvites->count(),
                    'invites' => $candidateInvites->toArray()
                ]);

                foreach ($candidateInvites as $invite) {
                    $deadline = Carbon::parse($invite['deadline']);
                    $isExpired = now()->greaterThan($deadline);
                    
                    \Log::debug('Checking invite expiration:', [
                        'test_id' => $invitation->test_id,
                        'invite' => $invite,
                        'deadline' => $deadline->toIso8601String(),
                        'current_time' => now()->toIso8601String(),
                        'is_expired' => $isExpired
                    ]);

                    if (!$isExpired) {
                        $validInvites->push([
                            'invitation' => $invitation,
                            'invite' => $invite
                        ]);
                    }
                }
            }

            \Log::debug('Valid non-expired invites:', [
                'count' => $validInvites->count(),
                'invites' => $validInvites->map(function($item) {
                    return [
                        'test_id' => $item['invitation']->test_id,
                        'invite' => $item['invite']
                    ];
                })
            ]);

            // Now process each valid invite and check candidate-tests table
            $candidateTests = $validInvites->map(function ($item) use ($candidate) {
                $invitation = $item['invitation'];
                
                // Check candidate-tests table
                $candidateTest = DB::table('candidate_test')
                    ->where('candidate_id', $candidate->id)
                    ->where('test_id', $invitation->test_id)
                    ->first();

                \Log::debug('Checking candidate_test table:', [
                    'test_id' => $invitation->test_id,
                    'candidate_id' => $candidate->id,
                    'record_found' => !is_null($candidateTest),
                    'record' => $candidateTest
                ]);

                // In your dashboard controller, modify the status string:
                if ($candidateTest) {
                    $testData = [
                        'title' => $invitation->test->title,
                        'test_id' => $invitation->test->id,
                        'status' => str_replace(' ', '_', $candidateTest->status), // Convert space to underscore
                        'started_at' => $candidateTest->started_at,
                        'completed_at' => $candidateTest->completed_at,
                        'score' => $candidateTest->score,
                        'questions_count' => $invitation->test->questions->count(),
                        'has_started' => true
                    ];
                    \Log::debug('Returning existing test data:', $testData);
                    return $testData;
                }

                // If no record in candidate_test table, return as not started
                $testData = [
                    'title' => $invitation->test->title,
                    'test_id' => $invitation->test->id,
                    'status' => 'not_started',
                    'started_at' => null,
                    'completed_at' => null,
                    'score' => null,
                    'questions_count' => $invitation->test->questions->count(),
                    'has_started' => false
                ];
                \Log::debug('Returning not started test data:', $testData);
                return $testData;
            });

            // Get all expired invites and add them to the results
            $expiredTests = $allInvitations->flatMap(function ($invitation) use ($candidate) {
                $invites = collect($invitation->invited_emails['invites']);
                $candidateInvites = $invites->where('email', $candidate->email);
                
                return $candidateInvites->filter(function ($invite) {
                    return now()->greaterThan(Carbon::parse($invite['deadline']));
                })->map(function ($invite) use ($invitation) {
                    $testData = [
                        'title' => $invitation->test->title,
                        'test_id' => $invitation->test->id,
                        'status' => 'expired',
                        'started_at' => null,
                        'completed_at' => null,
                        'score' => null,
                        'questions_count' => $invitation->test->questions->count(),
                        'has_started' => false
                    ];
                    \Log::debug('Adding expired test:', $testData);
                    return $testData;
                });
            });

            // Combine and sort all tests
            $allTests = $candidateTests->concat($expiredTests)->sortBy(function ($test) {
                $sortOrder = [
                    'in_progress' => 1,
                    'not_started' => 2,
                    'completed' => 3,
                    'accepted' => 4,
                    'rejected' => 5,
                    'expired' => 6
                ];
                return $sortOrder[$test['status']] ?? 7;
            });

            \Log::debug('Final test list:', [
                'total_count' => $allTests->count(),
                'status_breakdown' => $allTests->groupBy('status')
                    ->map(function($group) { return $group->count(); })
            ]);

            $invitation = $this->validateSession();
            
            return view('candidate.dashboard', [
                'candidateTests' => $allTests,
                'invitation' => $invitation
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in dashboard:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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