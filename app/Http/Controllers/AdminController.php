<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Test;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')->whereNotNull('completed_at')->distinct('candidate_id')->count(),
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

    public function manageCandidates()
    {
        $candidates = Candidate::with([
            'tests' => function ($query) {
                $query->select('tests.id', 'title', 'description', 'duration')
                    ->withPivot('started_at', 'completed_at', 'score', 'ip_address')
                    ->whereNotNull('candidate_test.completed_at'); // Add this line
            }
        ])
        ->whereHas('tests', function($query) {
            $query->whereNotNull('candidate_test.completed_at'); // Add this condition
        })
        ->select('id', 'name', 'email', 'created_at', 'updated_at')
        ->latest()
        ->paginate(10);
    
        foreach ($candidates as $candidate) {
            if ($test = $candidate->tests->first()) {
                $candidate->total_questions = $test->questions->count();
                $candidate->test_started_at = $test->pivot->started_at;
                $candidate->test_completed_at = $test->pivot->completed_at;
                $candidate->test_score = $test->pivot->score;
                $candidate->test_id = $test->id;
            }
        }
    
        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')
                ->whereNotNull('completed_at')
                ->distinct('candidate_id')
                ->count(),
            'activeTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
        ];
    
        return view('admin.manage-candidates', array_merge(compact('candidates'), $stats));
    }

    public function candidateResult(Candidate $candidate)
    {
        $test = $candidate->tests()
            ->with(['questions.choices', 'questions.media']) // Eager load relationships
            ->withPivot('started_at', 'completed_at', 'score','ip_address') // Specify pivot columns explicitly
            ->first();

        if (!$test) {
            return redirect()->back()->with('error', 'No test found for this candidate.');
        }
    
        $answers = Answer::where('candidate_id', $candidate->id)
            ->whereHas('question', function ($query) use ($test) {
                $query->where('test_id', $test->id);
            })->get();
    
    
        $totalQuestions = $test->questions->count();
        $percentage = $totalQuestions > 0 
            ? round(($test->pivot->score / $totalQuestions) * 100, 2) 
            : 0;
    
        return view('admin.candidate-result', [
            'candidate' => $candidate,
            'test' => $test,
            'testAttempt' => [
                'started_at' => $test->pivot->started_at,
                'completed_at' => $test->pivot->completed_at,
                'score' => $test->pivot->score
            ],
            'answers' => $answers,
            'totalQuestions' => $totalQuestions,
            'percentage' => $percentage
        ]);
    }    

    public function approveCandidate(Candidate $candidate)
    {
        $testAttempt = $candidate->tests()
            ->withPivot('completed_at', 'score') // Specify pivot columns explicitly
            ->latest()
            ->first();
    
        // if ($testAttempt) {
        //     Report::updateOrCreate( // Prevent duplicates
        //         [
        //             'candidate_id' => $candidate->id,
        //             'test_id' => $testAttempt->id,
        //         ],
        //         [
        //             'score' => $testAttempt->pivot->score,
        //             'completion_status' => 'approved',
        //             'date_completed' => $testAttempt->pivot->completed_at
        //         ]
        //     );
        // }
    
        return redirect()->back()->with('success', 'Candidate approved successfully.');
    }
    

    public function rejectCandidate(Candidate $candidate)
    {
        $testAttempt = $candidate->tests()
            ->withPivot('completed_at', 'score') // Specify pivot columns explicitly
            ->latest()
            ->first();
    
        // if ($testAttempt) {
        //     Report::updateOrCreate( // Prevent duplicates
        //         [
        //             'candidate_id' => $candidate->id,
        //             'test_id' => $testAttempt->id,
        //         ],
        //         [
        //             'score' => $testAttempt->pivot->score,
        //             'completion_status' => 'rejected',
        //             'date_completed' => $testAttempt->pivot->completed_at
        //         ]
        //     );
        // }
    
        return redirect()->back()->with('success', 'Candidate rejected successfully.');
    }

    public function manageReports()
    {
        $testReports = DB::table('candidate_test')
            ->select(
                'tests.id',
                'tests.title', 
                'tests.description',
                'candidate_test.test_id',
                DB::raw('COUNT(DISTINCT candidate_test.candidate_id) as total_candidates'),
                DB::raw('COUNT(candidate_test.report_path) as total_reports'),
                DB::raw('GROUP_CONCAT(candidate_test.report_path) as report_paths')
            )
            ->join('tests', 'tests.id', '=', 'candidate_test.test_id')
            ->whereNotNull('candidate_test.report_path')
            ->groupBy('tests.id', 'tests.title', 'tests.description', 'candidate_test.test_id')
            ->get()
            ->map(function($report) {
                $report->report_paths = explode(',', $report->report_paths);
                return $report;
            });
    
        return view('admin.manage-reports', [
            'testReports' => $testReports,
            'totalTests' => Test::count(),
            'totalReports' => DB::table('candidate_test')->whereNotNull('report_path')->count(),
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')
                ->whereNotNull('completed_at')
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
    
       $tempDir = storage_path('app/temp');
       if (!is_dir($tempDir)) {
           mkdir($tempDir, 0755, true);
       }
    
       $zipFileName = "test_{$testId}_reports_" . date('Y_m_d_His') . '.zip';
       $zipPath = "{$tempDir}/{$zipFileName}";
    
       $zip = new \ZipArchive();
       if ($zip->open($zipPath, \ZipArchive::CREATE)) {
           foreach ($reports as $report) {
               $reportPath = storage_path("app/reports/{$report}");
               if (file_exists($reportPath)) {
                   $zip->addFile($reportPath, $report);
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