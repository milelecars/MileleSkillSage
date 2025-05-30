<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Test;
use App\Models\Invitation;
use App\Models\Answer;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Google\Client;
use Google\Service\Gmail;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Services\TestReportService;


class AdminController extends Controller
{
    protected $testReportService;

    public function __construct(TestReportService $testReportService)
    {
        $this->testReportService = $testReportService;
    }

    private function getCompletedStatuses()
    {
        return ['completed', 'rejected', 'accepted'];
    }

    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count() ?? 0,
            'completedTests' => DB::table('candidate_test')
                ->whereIn('status', $this->getCompletedStatuses())
                ->distinct('candidate_id')
                ->count(),
            'activeTests' => Test::count() ?? 0,
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count() ?? 0,
        ];
        
        return view('admin.dashboard', $stats);
    }

    public function inviteCandidate(Request $request)
    {
        $tests = Test::all();
        $allTestIds = $tests->pluck('id')->toArray();
        $departments = Department::orderBy('name')->get();
    
        $query = Invitation::query();
    
        // Check if a candidate name is provided
        if ($request->has('candidate_name') && $request->candidate_name) {
            $candidateName = $request->candidate_name;
    
            // Join or filter candidates by name
            $query->whereHas('candidate', function ($q) use ($candidateName) {
                $q->where('name', 'like', '%' . $candidateName . '%');
            });
        }
    
        $emailToTestIds = $query->whereJsonLength('invited_emails->invites', '>', 0)
            ->get()
            ->flatMap(function ($invitation) {
                $invitedEmails = $invitation->invited_emails['invites'] ?? [];
                return collect($invitedEmails)->map(function ($inviteData) use ($invitation) {
                    return [
                        'email' => $inviteData['email'],
                        'test_id' => $invitation->test_id
                    ];
                });
            })
            ->groupBy('email')
            ->map(function ($group) {
                return $group->pluck('test_id')->unique()->values()->toArray();
            });
    
        // The opposite mapping (uninvited tests for each email)
        $emailToUninvitedTestIds = $emailToTestIds->map(function ($invitedTestIds) use ($allTestIds) {
            return array_values(array_diff($allTestIds, $invitedTestIds));
        });
    
        return view('admin.invite', compact('emailToTestIds', 'emailToUninvitedTestIds', 'tests', 'departments'));
    }

    public function sendInvitation(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $emailTestMap = $request->input('email_test_map');
            $role = $request->input('role');
            $department = $request->input('department');
            $oAuthController = new OAuthController();

            try {
                $client = $oAuthController->getClient();
                $service = new Gmail($client);
            } catch (\Exception $e) {
                return redirect()->route('google.login', ['testId' => array_values($emailTestMap)[0][0] ?? null]);
            }
    
            
            $template = file_get_contents(resource_path('views/emails/invitation-email-template.blade.php'));
            
            
            foreach ($emailTestMap as $email => $testIds) {
                if (empty($testIds)) continue;
    
                foreach ($testIds as $testId) {
                    try {
                        $invitation = Invitation::where('test_id', $testId)->firstOrFail();
                        $test = Test::findOrFail($testId);
    
                        
                        $htmlContent = view('emails.invitation-email-template', [
                            'testName' => $test->title,
                            'invitationLink' => $invitation->invitation_link,
                            'role' => $role,
                            'department' => $department,
                        ])->render();                        
    
                        $message = new \Google\Service\Gmail\Message();
                        
                        $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
                        $rawMessage .= "To: <{$email}>\r\n";
                        $rawMessage .= 'Subject: =?utf-8?B?' . base64_encode("Invitation to Take a Test for Milele Motors") . "?=\r\n";
                        $rawMessage .= "MIME-Version: 1.0\r\n";
                        $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
                        $rawMessage .= $htmlContent;
    
                        $message->setRaw(base64_encode($rawMessage));
                        
                        $service->users_messages->send('me', $message);
                        Log::info("Email sent successfully to {$email} for test {$testId}");
                        
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Failed to send invitation email to {$email} for test {$testId}: " . $e->getMessage());
                        
                        return redirect()
                            ->back()
                            ->withErrors(['email_error' => "Failed to send email to: {$email}"]);
                    }
                }
            }
    
        
            foreach ($emailTestMap as $email => $testIds) {
                if (empty($testIds)) continue;
            
                $candidate = DB::table('candidates')->where('email', $email)->first();

                if ($candidate && !empty($candidate->name)) {
                    $nameParts = explode(' ', trim($candidate->name), 2);
                    $firstName = $nameParts[0] ?? 'Unknown';
                    $lastName = $nameParts[1] ?? 'User';
                }

                foreach ($testIds as $testId) {
                    $invitation = Invitation::where('test_id', $testId)->firstOrFail();
                    
                    $currentInvites = $invitation->invited_emails['invites'] ?? [];
                    
                    // Check if email already exists
                    if (!collect($currentInvites)->contains('email', $email)) {
                        $currentInvites[] = [
                            'email' => $email,
                            'firstName' => $firstName,
                            'lastName' => $lastName,
                            'role' => $role,
                            'department' => $department,
                            'invited_at' => now()->toISOString(),
                            'deadline' => now()->addDays(2)->toISOString() 
                        ];
                        
                        $invitation->update([
                            'invited_emails' => ['invites' => $currentInvites]
                        ]);
                    }
                }
            }
    
            DB::commit();
            
            return redirect()
                ->route('admin.select-candidate', ['selected_email' => array_key_first($emailTestMap)])
                ->with('success', 'Invitations sent successfully!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process invitations: " . $e->getMessage());
            
            return redirect()
                ->back()
                ->withErrors(['submission' => 'Failed to process invitations. Please try again.']);
        }
    }

    private function getQuestions($test)
    {
        $test = Test::with([
            'questions' => function ($query) use ($candidate) {
                $query->with([
                    'answers' => function ($subQuery) use ($candidate) {
                        $subQuery->where('candidate_id', $candidate->id);
                    },
                    'choices',
                    'media'
                ]);
            },
            'admin'
        ])->findOrFail($id);

        
        $questions = $test->questions;

        return $questions;
    }

    private const STATUS_SORT_ORDER = [
        'completed' => 10,
        'in_progress' => 20,
        'accepted' => 30,
        'rejected' => 40,
        'not_started' => 50,
        'expired' => 100
    ];

    private function calculatePercentile(int $score, array $allScores): float {
        $count = count($allScores);
        if ($count === 0) return 0;

        $belowOrEqual  = count(array_filter($allScores, fn($s) => $s <= $score));
        return round(($belowOrEqual  / $count) * 100, 2);
    }

    public function exportCandidates(Request $request)
    {
        // Get the same data as your index method
        $search = $request->input('search');
        $testFilter = $request->input('test_filter');
        
        // Fetch candidates with the same filters as your main page
        $query = CandidateTest::query()
            ->with(['candidate', 'test'])
            ->join('candidates', 'candidate_test.candidate_id', '=', 'candidates.id')
            ->join('tests', 'candidate_test.test_id', '=', 'tests.id');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('candidates.name', 'like', '%' . $search . '%')
                ->orWhere('candidates.email', 'like', '%' . $search . '%');
            });
        }
        
        if ($testFilter) {
            $query->where('candidate_test.test_id', $testFilter);
        }
        
        $candidates = $query->get()->map(function ($item) {
            // Format the data as needed
            return [
                'Name' => $item->candidate->name ?? '',
                'Email' => $item->candidate->email,
                'Test' => $item->test->title,
                'Status' => ucfirst($item->status),
                'Started At' => $item->started_at ? \Carbon\Carbon::parse($item->started_at)->format('M d, Y H:i') : '',
                'Completed At' => $item->completed_at ? \Carbon\Carbon::parse($item->completed_at)->format('M d, Y H:i') : '',
                'Score' => $item->score ?? '',
                'Percentile' => isset($item->percentile) ? ($item->percentile >= 99 ? 'Top 1%' : ($item->percentile > 0 ? 'Top ' . (100 - floor($item->percentile)) . '%' : 'Bottom Performer')) : '',
            ];
        });
        
        // Create CSV content
        $headers = array_keys($candidates->first() ?? []);
        $csv = implode(',', $headers) . "\n";
        
        foreach ($candidates as $row) {
            $csv .= implode(',', array_map(function($field) {
                // Escape quotes and wrap fields in quotes
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }
        
        $filename = 'candidates_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function manageCandidates(Request $request)
    {

        $name = $request->input('name');
        $email = $request->input('email');
        $role = $request->input('role');
        $department = $request->input('department');
        $testFilter = $request->input('test_filter');

        Log::debug('Filters Received', [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'department' => $department,
            'testFilter' => $testFilter,
        ]);
        
        $activeTestCandidates = Candidate::with(['tests' => function ($query) use ($testFilter) {
            $query->select('tests.id', 'title', 'description', 'duration')
                ->when($testFilter, fn($q) => $q->where('tests.id', $testFilter))
                ->withPivot('role', 'department_id', 'started_at', 'completed_at', 'score', 'red_flags', 'correct_answers', 'wrong_answers', 'ip_address', 'status', 'is_suspended', 'unsuspend_count');
        }])
        ->when($testFilter, function ($query, $testFilter) {
            $query->whereHas('tests', fn($q) => $q->where('tests.id', $testFilter));
        })
        ->when($role, function ($query) use ($role) {
            $query->whereHas('tests', function ($q) use ($role) {
                $q->whereRaw('LOWER(candidate_test.role) = ?', [strtolower($role)]);
            });
        })
        ->when($department, function ($query) use ($department) {
            $query->whereHas('tests', function ($q) use ($department) {
                $departmentId = \App\Models\Department::whereRaw('LOWER(name) = ?', [strtolower($department)])->value('id');
                if ($departmentId) {
                    $q->where('candidate_test.department_id', $departmentId);
                }
            });
        })        
        ->when($name, function ($query) use ($name) {
            $query->where('name', 'like', "%$name%");
        })
        ->when($email, function ($query) use ($email) {
            $query->where('email', 'like', "%$email%");
        })
        ->select('id', 'name', 'email', 'created_at', 'updated_at')
        ->get()
        ->flatMap(function ($candidate) use ($role) {
            Log::debug('Filtering tests for candidate', [
                'candidate_id' => $candidate->id,
                'candidate_email' => $candidate->email,
                'all_roles' => $candidate->tests->pluck('pivot.role'),
                'role_filter' => $role,
            ]);
    
            return $candidate->tests->filter(function ($test) use ($role) {
                if (!$role) return true;
                return strtolower($test->pivot->role ?? '') === strtolower($role);
            })->map(function ($test) use ($candidate) {
                $status = $test->pivot->status;
                // If status is "not_started" and we have a record in candidate_test,
                // this means they've logged in
                $hasLoggedIn = $status === 'not started' && $test->pivot->created_at;
                $isSuspended = $test->pivot->is_suspended;
                $unsuspendCount = $test->pivot->unsuspend_count;

                $isExpired = false;

                if ($hasLoggedIn) {

                    $invitation = Invitation::where('test_id', $test->id)
                        ->whereJsonLength('invited_emails->invites', '>', 0)
                        ->get()
                        ->first(function ($invitation) use ($candidate) {
                            $invites = is_string($invitation->invited_emails) 
                                ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                                : ($invitation->invited_emails['invites'] ?? []);

                            return collect($invites)->contains(function ($invite) use ($candidate) {
                                return $invite['email'] === $candidate->email;
                            });
                        });

                    if ($invitation) {

                        $invites = is_string($invitation->invited_emails) 
                            ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                            : ($invitation->invited_emails['invites'] ?? []);

                        $candidateInvite = collect($invites)->first(function ($invite) use ($candidate) {
                            return $invite['email'] === $candidate->email;
                        });

                        if ($candidateInvite && isset($candidateInvite['deadline'])) {
                            $deadline = Carbon::parse($candidateInvite['deadline']);
                            $isExpired = now()->greaterThan($deadline);

                            if ($isExpired ) {
                                $status = 'expired';

                                DB::table('candidate_test')
                                    ->where('candidate_id', $candidate->id)
                                    ->where('test_id', $test->id)
                                    ->update(['status' => 'expired']);
                            }
                        } 
                    }
                }

                if ($test->title == "General Mental Ability (GMA)") {
                    $questions = $test->questions()
                        ->skip(8)
                        ->take(PHP_INT_MAX)
                        ->get();
                } else {
                    $questions = $test->questions;
                }
                 
                $hasMCQ = $questions->contains('question_type', 'MCQ');
                $hasLSQ = $questions->contains('question_type', 'LSQ');

                $percentileByTestId = DB::table('candidate_test')
                    ->select('test_id', 'candidate_id', 'score')
                    ->whereNotNull('score')
                    ->get()
                    ->groupBy('test_id')
                    ->map(function ($records) {
                        $allScores = $records->pluck('score')->toArray();
                        return $records->mapWithKeys(function ($item) use ($allScores) {
                            return [
                                $item->candidate_id => $this->calculatePercentile($item->score, $allScores)
                            ];
                        });
                    });

                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'role' => $test->pivot->role ?? '-',
                    'department' => \App\Models\Department::find($test->pivot->department_id)?->name ?? '-',
                    'test_title' => $test->title,
                    'test_id' => $test->id,
                    'status' => $status,
                    'is_suspended' => $isSuspended,
                    'unsuspend_count' => $unsuspendCount,
                    'started_at' => $test->pivot->started_at,
                    'completed_at' => $test->pivot->completed_at,
                    'expiration_date' => $isExpired ?? $candidateInvite['deadline'],
                    'score'=> $test->pivot->score,
                    'percentile' => $percentileByTestId[$test->id][$candidate->id] ?? null,
                    'hasMCQ' => $hasMCQ,
                    'hasLSQ' => $hasLSQ,
                    'red_flags'=> $test->pivot->red_flags,
                    'correct_answers' => $test->pivot->correct_answers,
                    'wrong_answers' => $test->pivot->wrong_answers,
                    'total_questions' => $questions->count(),
                    'has_started' => true,
                    'has_logged_in' => $hasLoggedIn,
                    'sort_order' => self::STATUS_SORT_ORDER[$status] ?? 99
                ];
                
            });
        });

        $takenTests = DB::table('candidate_test')
            ->join('candidates', 'candidates.id', '=', 'candidate_test.candidate_id')
            ->select('candidates.email', 'candidate_test.test_id')
            ->get()
            ->groupBy('email')
            ->map(function ($items) {
                return $items->pluck('test_id')->toArray();
            })
            ->toArray();

        $invitedEmailsRaw = Invitation::when($testFilter, function($query) use ($testFilter) {
            return $query->where('test_id', $testFilter);
        })
        ->whereJsonLength('invited_emails->invites', '>', 0)
        ->with('test:id,title')
        ->get()
        ->flatMap(function ($invitation) use ($name, $email, $role, $department, $takenTests) {
            $invites = is_string($invitation->invited_emails) 
                ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                : ($invitation->invited_emails['invites'] ?? []);
            
            return collect($invites)->map(function ($invite) use ($invitation, $name, $email, $role, $department, $takenTests) {
                $inviteEmail = strtolower($invite['email']);
                $inviteRole = strtolower($invite['role'] ?? '-');
                $inviteDepartment = strtolower($invite['department'] ?? '-');
                $deadline = Carbon::parse($invite['deadline']);
                $isExpired = now()->greaterThan($deadline);
        
                $matchesName = $name ? str_contains($inviteEmail, strtolower($name)) : true;
                $matchesEmail = $email ? str_contains($inviteEmail, strtolower($email)) : true;
                $matchesRole = $role ? strtolower($inviteRole) === strtolower($role) : true;
                $matchesDepartment = $department ? strtolower($inviteDepartment) === strtolower($department) : true;
            
                if ($matchesName && $matchesEmail && $matchesRole && $matchesDepartment) {

                    return [
                        'email' => $invite['email'],
                        'test_title' => $invitation->test->title ?? '-',
                        'role' => $invite['role'] ?? '-',
                        'department' => $invite['department'] ?? '-',
                        'test_id' => $invitation->test_id,
                        'status' => $isExpired ? 'expired' : 'invited',
                        'has_started' => false,
                        'has_logged_in' => false,
                        'invitation_id' => $invitation->id,
                        'expiration_date' => $invite['deadline'],
                        'sort_order' => self::STATUS_SORT_ORDER[$isExpired ? 'expired' : 'invited'] ?? 99,
                        'is_invitation' => true
                    ];
                }
        
                return null;
            })->filter();
        });
        
        $takenTestsFlat = DB::table('candidate_test')
            ->select('candidate_id', 'test_id', 'candidates.email')
            ->join('candidates', 'candidates.id', '=', 'candidate_test.candidate_id')
            ->get()
            ->map(function ($record) {
                return [
                    'email' => strtolower($record->email),
                    'test_id' => $record->test_id
                ];
            })
            ->values();
        
        $invitedEmails = $invitedEmailsRaw->filter(function ($invite) use ($takenTestsFlat) {
            $exists = $takenTestsFlat->contains(function ($taken) use ($invite) {
                return strtolower($invite['email']) === $taken['email']
                    && $invite['test_id'] == $taken['test_id'];
            });
        
            return !$exists;
        });
        

        $availableTests = Test::select('id', 'title')->get();

        $totalInvited = Invitation::when($testFilter, function($query) use ($testFilter) {
                return $query->where('test_id', $testFilter);
            })
            ->whereJsonLength('invited_emails->invites', '>', 0)
            ->get()
            ->sum(function ($invitation) {
                $invites = is_string($invitation->invited_emails) 
                    ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                    : ($invitation->invited_emails['invites'] ?? []);
                return count($invites) ?? 0;
            });
            
        $completedByTest = DB::table('candidate_test')
            ->when($testFilter, function($query) use ($testFilter) {
                return $query->where('test_id', $testFilter);
            })
            ->select('test_id', DB::raw('COUNT(*) as completed_count'))
            ->where('status', 'completed')
            ->groupBy('test_id')
            ->pluck('completed_count', 'test_id')
            ->toArray();

        $allCandidates = $activeTestCandidates->concat($invitedEmails);

        $allCandidates = $testFilter
            ? $allCandidates->sortByDesc(fn($item) => $item['percentile'] ?? 0)
            : $allCandidates->sortBy(fn($item) => [
                $item['sort_order'],
                !($item['has_logged_in'] ?? false),
                !isset($item['name']),
                $item['email']
            ]);
        
        $candidates = $allCandidates->values();
        
        
        $stats = [
            'totalInvited' => $allCandidates->count(),
        
            'activeTests' => $candidates->filter(function ($c) {
                    $status = strtolower(trim($c['status']));
                    return in_array($status, ['not started', 'in progress', 'invited']);
            })->count(),

        
            'completedTestsCount' => $candidates->filter(function ($c) {
                return in_array(strtolower(trim($c['status'])), ['completed', 'accepted', 'rejected']);
            })->count(),
        
           'totalTests' => \App\Models\Test::whereIn('id', $candidates->pluck('test_id')->unique())
                ->whereNull('deleted_at')
                ->count(),


        ];        

        $departments = \App\Models\Department::select('name')->orderBy('name')->get();

        return view('admin.manage-candidates', array_merge(
            compact('candidates', 'name', 'email', 'role', 'department', 'availableTests', 'testFilter', 'departments'), 
            $stats
        ));
    }

    public function deleteCandidate($candidateId, $testId)
    {
        try {
            DB::beginTransaction();
            
            $candidate = Candidate::findOrFail($candidateId);
            
            Log::info('Starting deletion of candidate test record', [
                'candidate_id' => $candidateId,
                'test_id' => $testId,
                'candidate_email' => $candidate->email
            ]);
            
            // Delete answers
            $answersDeleted = DB::table('answers')
                ->where('candidate_id', $candidateId)
                ->where('test_id', $testId)
                ->delete();
                
            Log::info('Answers deleted', [
                'count' => $answersDeleted,
                'candidate_id' => $candidateId,
                'test_id' => $testId
            ]);
            
            // Delete flags
            $flagsDeleted = DB::table('candidate_flags')
                ->where('candidate_id', $candidateId)
                ->where('test_id', $testId)
                ->delete();
                
            Log::info('Candidate flags deleted', [
                'count' => $flagsDeleted,
                'candidate_id' => $candidateId,
                'test_id' => $testId
            ]);
            
            // Delete screenshots
            $screenshotsDeleted = DB::table('candidate_test_screenshots')
                ->join('candidate_test', function($join) use ($candidateId, $testId) {
                    $join->where('candidate_test.candidate_id', $candidateId)
                        ->where('candidate_test.test_id', $testId);
                })
                ->delete();
                
            Log::info('Screenshots deleted', [
                'count' => $screenshotsDeleted,
                'candidate_id' => $candidateId,
                'test_id' => $testId
            ]);
            
            // Delete candidate test record
            $testRecordDeleted = DB::table('candidate_test')
                ->where('candidate_id', $candidateId)
                ->where('test_id', $testId)
                ->delete();
                
            Log::info('Candidate test record deleted', [
                'count' => $testRecordDeleted,
                'candidate_id' => $candidateId,
                'test_id' => $testId
            ]);

            // Remove the email from invitations JSON
            $invitation = DB::table('invitations')
                ->where('test_id', $testId)
                ->first();

            if ($invitation) {
                $invitedEmails = json_decode($invitation->invited_emails, true);
                
                // Filter out the candidate's email
                $invitedEmails['invites'] = array_values(array_filter(
                    $invitedEmails['invites'], 
                    function($invite) use ($candidate) {
                        return $invite['email'] !== $candidate->email;
                    }
                ));
                
                // Update the invitation record
                DB::table('invitations')
                    ->where('test_id', $testId)
                    ->update([
                        'invited_emails' => json_encode($invitedEmails)
                    ]);
                    
                Log::info('Removed email from invitations', [
                    'email' => $candidate->email,
                    'test_id' => $testId
                ]);
            }
            
            DB::commit();
            
            Log::info('Successfully completed deletion of candidate test record', [
                'candidate_id' => $candidateId,
                'test_id' => $testId
            ]);
            
            return redirect()->back()->with('success', 'Candidate test record deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete candidate test record', [
                'candidate_id' => $candidateId,
                'test_id' => $testId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete candidate test record. Please try again.');
        }
    }

    public function getPrivateScreenshot($testId, $candidateId, $filename)
    {
        try {
            // Find the screenshot record with the correct table name
            $screenshot = DB::table('candidate_test_screenshots')
                ->where('test_id', $testId)
                ->where('candidate_id', $candidateId)
                ->where('screenshot_path', 'like', '%' . $filename)
                ->first();
    
            Log::info('Screenshot query result', [
                'screenshot' => $screenshot,
                'testId' => $testId,
                'candidateId' => $candidateId,
                'filename' => $filename
            ]);
    
            if (!$screenshot) {
                Log::error('Screenshot not found in database');
                abort(404);
            }
    
            $fullPath = storage_path("app/private/" . $screenshot->screenshot_path);
            
            Log::info('File path check', [
                'fullPath' => $fullPath,
                'exists' => file_exists($fullPath)
            ]);
    
            if (!file_exists($fullPath)) {
                Log::error('File not found on disk', [
                    'path' => $fullPath
                ]);
                abort(404);
            }
    
            return response()->file($fullPath, [
                'Content-Type' => mime_content_type($fullPath),
                'Cache-Control' => 'private, no-cache, no-store, must-revalidate'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Screenshot error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function candidateResult(Test $test, Candidate $candidate)
    {
        $test = $candidate->tests()
            ->where('candidate_test.test_id', $test->id)
            ->where('candidate_test.candidate_id', $candidate->id)
            ->with(['questions.choices', 'questions.media'])
            ->withPivot('started_at', 'completed_at', 'score', 'red_flags', 'correct_answers', 'wrong_answers', 'ip_address', 'status')
            ->first();
    
        if (!$test) {
            Log::error('No test found for candidate', ['candidateId' => $candidate->id]);
            return redirect()->back()->with('error', 'No test found for this candidate.');
        }
    
        if ($test->pivot->started_at && ($test->pivot->completed_at || $test->pivot->is_suspended)) {
            $startedAt = Carbon::parse($test->pivot->started_at);
            $completedAt = Carbon::parse($test->pivot->completed_at);
            $duration = $startedAt->diff($completedAt);
            $durationInMinutes = $duration->days * 24 * 60 + $duration->h * 60 + $duration->i;
            $durationInSeconds = $duration->s;
            $duration = $durationInMinutes . ' ' . Str::plural('minute', $durationInMinutes) . ' and ' . $durationInSeconds . ' ' . Str::plural('second', $durationInSeconds);
        }else{
            $startedAt = Carbon::parse($test->pivot->started_at);
            $completedAt = Carbon::parse($test->pivot->completed_at);
            $duration = 0;
        }

        $directory = storage_path("app/private/screenshots/test{$test->id}/candidate{$candidate->id}");

        if (File::exists($directory)) {
            $files = File::files($directory);

            $screenshots = collect($files)->map(function ($file) use ($test, $candidate) {
                return [
                    'id' => null,
                    'screenshot_path' => $file->getFilename(),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()), 
                ];
            })->sortBy('created_at')->values();

        } else {
            $screenshots = collect();
        }

        Log::info('Retrieved screenshots', [
            'count' => $screenshots->count(),
            'paths' => $screenshots->pluck('screenshot_path')->toArray()
        ]);


    
        if ($test->title == "General Mental Ability (GMA)") {
            $questions = $test->questions()
                ->skip(8)
                ->take(PHP_INT_MAX)
                ->get();
        } else {
            $questions = $test->questions;
        }
        
        $totalQuestions = $questions->count() ?? 0;
        $originalScore = $test->pivot->score;
        $hasMCQ = $questions->contains('question_type', 'MCQ');
        $hasLSQ = $questions->contains('question_type', 'LSQ');
        $suspensionReason = $test->pivot->suspension_reason;
        $realIP = $this->testReportService->getClientIP();
        $location = $this->testReportService->getLocationFromIP($realIP);
        
        $categoryScores = [];

        if ($hasLSQ) {
            $lsqAnswers = \App\Models\Answer::with('question')
                ->where('candidate_id', $candidate->id)
                ->where('test_id', $test->id)
                ->whereHas('question', function ($query) {
                    $query->where('question_type', 'LSQ');
                })
                ->get();

            foreach ($lsqAnswers as $answer) {
                $question = $answer->question;

                if (!$question || !$question->category) {
                    continue;
                }

                $category = $question->category;
                $rawScore = (int) $answer->answer_text;
                $score = $question->reverse ? (6 - $rawScore) : $rawScore;

                $categoryScores[$category]['total'] = ($categoryScores[$category]['total'] ?? 0) + $score;
                $categoryScores[$category]['count'] = ($categoryScores[$category]['count'] ?? 0) + 1;
            }

            foreach ($categoryScores as $cat => $data) {
                $categoryScores[$cat]['average'] = round($data['total'] / $data['count'], 2);
            }
        }

            
        return view('admin.candidate-result', compact(
            'candidate',
            'test',
            'location',
            'screenshots',
            'totalQuestions',
            'originalScore',
            'duration',
            'hasMCQ',
            'hasLSQ',
            'suspensionReason',
            'categoryScores'
        ));
    }
    

    private function sendEmailWithGmail($candidate, $template, $subject, $test = null)
    {
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
            $service = new Gmail($client);

            // Get email template and replace variables
            $template = file_get_contents(resource_path("views/emails/{$template}.blade.php"));
            $htmlContent = str_replace(
                ['{{ $candidate->name }}', '{{ $testName }}'],
                [$candidate->name, ($test ? $test->title : 'N/A')],
                $template
            );

            $message = new \Google\Service\Gmail\Message();
            
            $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
            $rawMessage .= "To: <{$candidate->email}>\r\n";
            $rawMessage .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
            $rawMessage .= $htmlContent;

            $message->setRaw(base64_encode($rawMessage));
            
            $service->users_messages->send('me', $message);
            Log::info("Status email sent successfully to {$candidate->email}");
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send status email to {$candidate->email}: " . $e->getMessage());
            throw $e;
        }
    }

    public function acceptCandidate(Candidate $candidate)
    {
        try {
            DB::beginTransaction();
            
            // First try to send the email
            $emailSent = $this->sendEmailWithGmail(
                $candidate, 
                'candidate-acceptance-template',
                'Your Application Status - Milele Motors'
            );
    
            // Only if email was sent successfully, update the database
            if ($emailSent) {
                $testId = request('test_id');
                $candidate->tests()
                    ->wherePivot('test_id', $testId)
                    ->updateExistingPivot($testId, ['status' => 'accepted']);
                    
                DB::commit();
                return redirect()->back()->with('success', 'Candidate accepted and notified successfully.');
            } else {
                DB::rollback();
                return redirect()->back()->with('error', 'Failed to send notification email. Status not updated.');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to accept candidate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process acceptance. Please try again.');
        }
    }

    public function rejectCandidate(Candidate $candidate)
    {
        try {
            DB::beginTransaction();
            
            // First try to send the email
            $emailSent = $this->sendEmailWithGmail(
                $candidate, 
                'candidate-rejection-template',
                'Your Application Status - Milele Motors'
            );
    
            // Only if email was sent successfully, update the database
            if ($emailSent) {
                $testId = request('test_id');
                $candidate->tests()
                    ->wherePivot('test_id', $testId)
                    ->updateExistingPivot($testId, ['status' => 'rejected']);
                    
                DB::commit();
                return redirect()->back()->with('success');
            } else {
                DB::rollback();
                return redirect()->back()->with('error', 'Failed to send notification email. Status not updated.');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reject candidate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to process rejection. Please try again.');
        }
    }

    public function unsuspendTest($candidateId, $testId, Request $request)
    {
        try {
            DB::beginTransaction();

            $candidateTest = DB::table('candidate_test')
                ->where('candidate_id', $candidateId)
                ->where('test_id', $testId)
                ->first();

            $candidate = DB::table('candidates')
            ->where('id', $candidateId)->first();

            $test = DB::table('tests')
            ->where('id', $testId)
            ->first();

            Log::info([$candidate]);

            if (!$candidateTest) {
                return redirect()->back()->with('error', 'Candidate test record not found.');
            }

            // First try to send the email
            $emailSent = $this->sendEmailWithGmail(
                $candidate, 
                'candidate-unsuspension-template',
                'Your Test Status - Milele Motors',
                $test,
            );

            // Check if the test has already been unsuspended
            // if ($candidateTest->unsuspend_count >= 1) {
            //     return redirect()->back()->with('error', 'This test can only be unsuspended once.');
            // }

            if ($emailSent) {
                DB::table('candidate_test')
                    ->where('candidate_id', $candidateId)
                    ->where('test_id', $testId)
                    ->update([
                        'is_suspended' => false, 
                        'unsuspend_count' => $candidateTest->unsuspend_count + 1, 
                        'suspended_at' => null, 
                        'suspension_reason' => null, 
                        'evidence_path' => null, 
                        'started_at' => null, 
                        'status' => 'not started'
                    ]);
    
                DB::commit();
    
                return redirect()->back()->with('success', 'Test unsuspended successfully.');
            } else {
                DB::rollback();
                return redirect()->back()->with('error', 'Failed to send notification email. Status not updated.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to unsuspend test:', [
                'candidate_id' => $candidateId,
                'test_id' => $testId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to unsuspend test. Please try again.');
        }
    }

    public function manageReports()
    {
        $testReports = DB::table('tests')
            ->select(
                'tests.id',
                'tests.title',
                DB::raw('COUNT(DISTINCT CASE WHEN candidate_test.status IN ("' . implode('","', $this->getCompletedStatuses()) . '") THEN candidate_test.candidate_id END) as completed_count'),
                DB::raw('COUNT(DISTINCT CASE WHEN candidate_test.report_path IS NOT NULL THEN candidate_test.candidate_id END) as total_reports')
            )
            ->leftJoin('candidate_test', 'tests.id', '=', 'candidate_test.test_id')
            ->groupBy('tests.id', 'tests.title')
            ->get()
            ->map(function($report) {
                $invitation = DB::table('invitations')
                    ->where('test_id', $report->id)
                    ->first();
            
                \Log::info('Invitation Data: ', (array) $invitation);
            
                $invitedEmails = $invitation && $invitation->invited_emails
                    ? json_decode($invitation->invited_emails, true)
                    : ['invites' => []]; 

                \Log::info('Invited Emails Data: ', ['invitedEmails' => $invitedEmails]);
            
                $totalInvited = is_array($invitedEmails) ? count($invitedEmails['invites'] ?? []) : 0;
            
                $report->total_invited = $totalInvited;
                $report->remaining_invites = $totalInvited - $report->completed_count;
                $report->invitation_expiry = $invitation ? $invitation->expiration_date : null;
            
                return $report;
            });
            
    
        return view('admin.manage-reports', [
            'testReports' => $testReports,
            'totalTests' => Test::count() ?? 0,
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count() ?? 0,
            'totalCandidatesParticipated' => DB::table('candidate_test')
                ->whereIn('status', $this->getCompletedStatuses())
                ->distinct('candidate_id')
                ->count() ?? 0
        ]);
    }
    
    public function downloadTestReports($testId) 
    {
        $reports = DB::table('candidate_test')
            ->where('test_id', $testId)
            ->whereNotNull('report_path')
            ->where('report_path', 'LIKE', '%test' . $testId . '_%')
            ->pluck('report_path')
            ->toArray();
    
        if (empty($reports)) {
            return back()->with('error', 'No reports found for this test.');
        }
    
        $tempDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                return back()->with('error', 'Could not create temp directory');
            }
        }

        $test = DB::table('tests')->where('id', $testId)->first();
    
        $zipFileName = "{$test->title}_reports_" . date('Y_m_d') . '.zip';
        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;
    
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE)) {
            foreach ($reports as $report) {
                // Use the public directory path
                $reportFileName = basename($report);
                $reportPath = storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $reportFileName);
                
                if (file_exists($reportPath)) {
                    $zip->addFile($reportPath, $reportFileName);
                } else {
                    \Log::error("File not found: " . $reportPath); // For debugging
                }
            }
            
            $zip->close();
    
            if (file_exists($zipPath)) {
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }
        }
    
        return back()->with('error', 'Could not create zip file.');
    }
}