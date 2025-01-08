<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Test;
use App\Models\Invitation;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AcceptanceEmail;
use App\Mail\RejectionEmail;
use App\Mail\InvitationEmail;
use Google\Client;
use Google\Service\Gmail;


class AdminController extends Controller
{
    private function getCompletedStatuses()
    {
        return ['completed', 'rejected', 'accepted'];
    }

    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')
                ->whereIn('status', $this->getCompletedStatuses())
                ->distinct('candidate_id')
                ->count(),
            'activeTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
        ];
        
        return view('admin.dashboard', $stats);
    }

    public function inviteCandidate()
    {
        $tests = Test::all();
        $allTestIds = $tests->pluck('id')->toArray();
        
        $emailToTestIds = Invitation::whereJsonLength('invited_emails', '>', 0)
            ->get()
            ->flatMap(function ($invitation) {
                $invitedEmailsList = is_string($invitation->invited_emails) 
                    ? json_decode($invitation->invited_emails, true) 
                    : $invitation->invited_emails;
                
                return collect($invitedEmailsList)->map(function ($email) use ($invitation) {
                    return [
                        'email' => $email,
                        'test_id' => $invitation->test_id
                    ];
                });
            })
            ->groupBy('email')
            ->map(function ($group) {
                return $group->pluck('test_id')->unique()->values()->toArray();
            });

            
        // Create the opposite mapping (uninvited tests for each email)
        $emailToUninvitedTestIds = $emailToTestIds->map(function ($invitedTestIds) use ($allTestIds) {
            return array_values(array_diff($allTestIds, $invitedTestIds));
        });


        return view('admin.invite', compact('emailToTestIds', 'emailToUninvitedTestIds', 'tests'));
    }

    public function sendInvitation(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $emailTestMap = $request->input('email_test_map');
            $oAuthController = new OAuthController();
            
            try {
                $client = $oAuthController->getClient();
                $service = new Gmail($client);
            } catch (\Exception $e) {
                return redirect()->route('google.login', ['testId' => array_values($emailTestMap)[0][0] ?? null]);
            }
    
            // Get email template
            $template = file_get_contents(resource_path('views/emails/invitation-email-template.blade.php'));
            
            // Try to send all emails
            foreach ($emailTestMap as $email => $testIds) {
                if (empty($testIds)) continue;
    
                foreach ($testIds as $testId) {
                    try {
                        $invitation = Invitation::where('test_id', $testId)->firstOrFail();
                        $test = Test::findOrFail($testId);
    
                        // Replace variables in template
                        $htmlContent = str_replace(
                            ['{{ $testName }}', '{{ $invitationLink }}'],
                            [$test->title, $invitation->invitation_link],
                            $template
                        );
    
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
    
            // All emails were sent successfully, update database
            foreach ($emailTestMap as $email => $testIds) {
                if (empty($testIds)) continue;
    
                foreach ($testIds as $testId) {
                    $invitation = Invitation::where('test_id', $testId)->firstOrFail();
                    
                    $existingEmails = is_array($invitation->invited_emails) 
                        ? $invitation->invited_emails 
                        : (json_decode($invitation->invited_emails, true) ?: []);
    
                    if (!in_array($email, $existingEmails)) {
                        $existingEmails[] = $email;
                        
                        $invitation->update([
                            'invited_emails' => $existingEmails
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
            'questions.choices',
            'questions.media',
            'questions.answers' => function($query) use ($candidate) {
                $query->where('candidate_id', $candidate->id);
            }
        ])->findOrFail($id);
        $questions = $test->questions;

        return $questions;
    }

    public function manageCandidates(Request $request)
    {
        $search = $request->input('search');
        $testFilter = $request->input('test_filter'); // Add test filter

        $activeTestCandidates = Candidate::with(['tests' => function ($query) {
            $query->select('tests.id', 'title', 'description', 'duration')
                ->withPivot('started_at', 'completed_at', 'score', 'ip_address', 'status');
        }])
        ->whereHas('tests', function($query) use ($testFilter) {
            if ($testFilter) {
                $query->where('tests.id', $testFilter);
            }
        })
        ->select('id', 'name', 'email', 'created_at', 'updated_at')
        ->when($search, function($query) use ($search) {
            return $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->get()
        ->flatMap(function ($candidate) {
            return $candidate->tests->map(function ($test) use ($candidate) {
                return [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'test_title' => $test->title,
                    'test_id' => $test->id,
                    'status' => $test->pivot->status,
                    'started_at' => $test->pivot->started_at,
                    'completed_at' => $test->pivot->completed_at,
                    'score' => $test->pivot->score,
                    'total_questions' => $test->questions->count(),
                    'has_started' => true,
                    'sort_order' => $test->pivot->status === 'completed' ? 1 : 
                                ($test->pivot->status === 'in_progress' ? 2 : 
                                ($test->pivot->status === 'accepted' ? 3 : 
                                ($test->pivot->status === 'rejected' ? 4 : 5)))
                ];
            });
        });

        // Modified invitedEmails query to respect test filter
        $takenTests = DB::table('candidate_test')
            ->join('candidates', 'candidates.id', '=', 'candidate_test.candidate_id')
            ->select('candidates.email', 'candidate_test.test_id')
            ->get()
            ->groupBy('email')
            ->map(function ($items) {
                return $items->pluck('test_id')->toArray();
            })
            ->toArray();

        $invitedEmails = Invitation::when($testFilter, function($query) use ($testFilter) {
                return $query->where('test_id', $testFilter);
            })
            ->whereJsonLength('invited_emails', '>', 0)
            ->with('test:id,title')
            ->get()
            ->flatMap(function ($invitation) use ($search, $takenTests) {
                $invitedEmailsList = is_string($invitation->invited_emails) 
                    ? json_decode($invitation->invited_emails, true) 
                    : $invitation->invited_emails;

                return collect($invitedEmailsList)->map(function ($email) use ($invitation, $search, $takenTests) {
                    if ((!$search || str_contains(strtolower($email), strtolower($search))) 
                        && (!isset($takenTests[$email]) || !in_array($invitation->test_id, $takenTests[$email]))) {
                        return [
                            'email' => $email,
                            'test_title' => $invitation->test->title,
                            'test_id' => $invitation->test_id,
                            'status' => 'not_started',
                            'has_started' => false,
                            'invitation_id' => $invitation->id,
                            'sort_order' => 6
                        ];
                    }
                    return null;
                })->filter();
            });

        // Get all available tests for the filter dropdown
        $availableTests = Test::select('id', 'title')->get();

        // Rest of the stats calculations...
        $totalInvited = Invitation::when($testFilter, function($query) use ($testFilter) {
                return $query->where('test_id', $testFilter);
            })
            ->whereJsonLength('invited_emails', '>', 0)
            ->get()
            ->sum(function ($invitation) {
                $emails = is_string($invitation->invited_emails) 
                    ? json_decode($invitation->invited_emails, true) 
                    : $invitation->invited_emails;
                return count($emails);
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

        $allCandidates = $activeTestCandidates->concat($invitedEmails)
            ->sortBy('sort_order');

        $candidates = new \Illuminate\Pagination\LengthAwarePaginator(
            $allCandidates->forPage(request()->get('page', 1), 10),
            $allCandidates->count(),
            10,
            request()->get('page', 1)
        );

        $candidates->withPath(request()->url());

        $stats = [
            'totalInvited' => $totalInvited,
            'completedTestsCount' => array_sum($completedByTest),
            'completedByTest' => $completedByTest,
            'activeTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')
                ->when($testFilter, function($query) use ($testFilter) {
                    return $query->where('test_id', $testFilter);
                })
                ->whereNotNull('report_path')
                ->count(),
        ];

        return view('admin.manage-candidates', array_merge(
            compact('candidates', 'search', 'availableTests', 'testFilter'), 
            $stats
        ));
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

    public function candidateResult(Test $test, Candidate $candidate )
    {
        $test = $candidate->tests()
            ->where('candidate_test.test_id', $test->id)
            ->where('candidate_test.candidate_id', $candidate->id)
            ->with(['questions.choices', 'questions.media'])
            ->withPivot('started_at', 'completed_at', 'score', 'ip_address', 'status')
            ->first();

        if (!$test) {
            Log::error('No test found for candidate', ['candidateId' => $candidate->id]);
            return redirect()->back()->with('error', 'No test found for this candidate.');
        }

        if ($test->pivot->started_at && $test->pivot->completed_at) {
            $startedAt = Carbon::parse($test->pivot->started_at);
            $completedAt = Carbon::parse($test->pivot->completed_at);
            $duration = $startedAt->diff($completedAt);
            $durationInMinutes = $duration->days * 24 * 60 + $duration->h * 60 + $duration->i;
            $durationInSeconds = $duration->s;
        }

        $duration = $durationInMinutes . ' ' . Str::plural('minute', $durationInMinutes) . ' and ' . $durationInSeconds . ' ' . Str::plural('second', $durationInSeconds);

        $screenshots = DB::table('candidate_test_screenshots')
            ->where('candidate_id', $candidate->id)
            ->where('test_id', $test->id)
            ->select('id', 'screenshot_path', 'created_at')
            ->orderBy('created_at', 'asc')
            ->get();

        Log::info('Retrieved screenshots', [
            'count' => $screenshots->count(),
            'paths' => $screenshots->pluck('screenshot_path')->toArray()
        ]);

        $totalQuestions = $test->questions->count();
        $percentage = $totalQuestions > 0 
            ? round(($test->pivot->score / $totalQuestions) * 100) 
            : 0;


        return view('admin.candidate-result', compact(
            'candidate',
            'test',
            'screenshots',
            'totalQuestions',
            'percentage',
            'duration'
        ));
    }

    private function sendEmailWithGmail($candidate, $template, $subject)
    {
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
            $service = new Gmail($client);

            // Get email template and replace variables
            $template = file_get_contents(resource_path("views/emails/{$template}.blade.php"));
            $htmlContent = str_replace(
                '{{ $candidate->name }}',
                $candidate->name,
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
                return redirect()->back()->with('success', 'Candidate rejected and notified successfully.');
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
                
                $invitedEmails = $invitation ? json_decode($invitation->invited_emails, true) : [];
                $totalInvited = count($invitedEmails);
                
                $report->total_invited = $totalInvited;
                $report->remaining_invites = $totalInvited - $report->completed_count;
                $report->invitation_expiry = $invitation ? $invitation->expiration_date : null;
                
                return $report;
            });
    
        return view('admin.manage-reports', [
            'testReports' => $testReports,
            'totalTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
            'totalCandidatesParticipated' => DB::table('candidate_test')
                ->whereIn('status', $this->getCompletedStatuses())
                ->distinct('candidate_id')
                ->count()
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