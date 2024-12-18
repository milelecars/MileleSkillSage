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


class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')->where('status', 'completed')->distinct('candidate_id')->count(),
            'activeTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
        ];
        
        return view('admin.dashboard', $stats);
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
    
        $activeTestCandidates = Candidate::with(['tests' => function ($query) {
            $query->select('tests.id', 'title', 'description', 'duration')
                ->withPivot('started_at', 'completed_at', 'score', 'ip_address', 'status');
        }])
        ->whereHas('tests')
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
    
        $takenTests = DB::table('candidate_test')
            ->join('candidates', 'candidates.id', '=', 'candidate_test.candidate_id')
            ->select('candidates.email', 'candidate_test.test_id')
            ->get()
            ->groupBy('email')
            ->map(function ($items) {
                return $items->pluck('test_id')->toArray();
            })
            ->toArray();
    
        $invitedEmails = Invitation::whereJsonLength('invited_emails', '>', 0)
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
    
        $totalInvited = Invitation::whereJsonLength('invited_emails', '>', 0)
            ->get()
            ->sum(function ($invitation) {
                $emails = is_string($invitation->invited_emails) 
                    ? json_decode($invitation->invited_emails, true) 
                    : $invitation->invited_emails;
                return count($emails);
            });
    
        $completedByTest = DB::table('candidate_test')
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
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
        ];
    
        return view('admin.manage-candidates', array_merge(compact('candidates', 'search'), $stats));
    }
    public function getPrivateScreenshot($testId, $candidateId, $filename)
    {
        $path = "screenshots/{$testId}/{$candidateId}/{$filename}";
        Log::info('Constructed path', ['path' => $path]);

        if (!Storage::disk('private')->exists($path)) {
            Log::error('File not found in private storage', [
                'path' => $path,
                'fullPath' => Storage::disk('private')->path($path),
            ]);
            abort(404);
        }

        $fullPath = Storage::disk('private')->path($path);
        $mimeType = mime_content_type($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function candidateResult(Candidate $candidate)
    {
        $test = $candidate->tests()
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

    public function acceptCandidate(Candidate $candidate)
    {
        try {
        DB::beginTransaction();
        
        $testId = request('test_id');
        
        $candidate->tests()
            ->wherePivot('test_id', $testId)
            ->updateExistingPivot($testId, ['status' => 'accepted']);
            
        Mail::to($candidate->email)->send(new AcceptanceEmail($candidate));
        
        DB::commit();
        return redirect()->back()->with('success', 'Candidate accepted and notified successfully.');
        
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to accept candidate: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to accept candidate. Please try again.');
        }
    }

    public function rejectCandidate(Candidate $candidate)
    {
    try {
        DB::beginTransaction();
        
        $testId = request('test_id');
        
        $candidate->tests()
            ->wherePivot('test_id', $testId)
            ->updateExistingPivot($testId, ['status' => 'rejected']);
            
        Mail::to($candidate->email)->send(new RejectionEmail($candidate));
        
        DB::commit();
        return redirect()->back()->with('success', 'Candidate rejected and notified successfully.');
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Failed to reject candidate: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to reject candidate. Please try again.');
    }
    }

    public function manageReports()
    {
        $testReports = DB::table('tests')
            ->select(
                'tests.id',
                'tests.title',
                DB::raw('COUNT(DISTINCT CASE WHEN candidate_test.status = "completed" THEN candidate_test.candidate_id END) as completed_count'),
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
                ->where('status', 'completed')
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