<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Test;
use App\Models\Answer;
use App\Models\Report;
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
            'activeTests' => Test::count()
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

                    ->withPivot('started_at', 'completed_at', 'score','ip_address');

            },
            'tests.questions',
            'reports' => function ($query) {
                $query->select('id', 'candidate_id', 'test_id', 'score', 'completion_status', 'date_completed');
            }
        ])
        ->select('id', 'name', 'email', 'created_at', 'updated_at')
        ->latest()
        ->paginate(10);

        foreach ($candidates as $candidate) {
            if ($test = $candidate->tests->first()) {
                $candidate->total_questions = $test->questions->count();
                $candidate->test_started_at = $test->pivot->started_at;
                $candidate->test_completed_at = $test->pivot->completed_at;
                $candidate->test_score = $test->pivot->score;
            }
        }

        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => DB::table('candidate_test')
                ->whereNotNull('completed_at')
                ->distinct('candidate_id')
                ->count(),
            'activeTests' => Test::count(),
            'totalReports' => Report::count()
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
    
        $report = Report::where('candidate_id', $candidate->id)
            ->where('test_id', $test->id)
            ->first();
    
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
            'report' => $report,
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
    
        if ($testAttempt) {
            Report::updateOrCreate( // Prevent duplicates
                [
                    'candidate_id' => $candidate->id,
                    'test_id' => $testAttempt->id,
                ],
                [
                    'score' => $testAttempt->pivot->score,
                    'completion_status' => 'approved',
                    'date_completed' => $testAttempt->pivot->completed_at
                ]
            );
        }
    
        return redirect()->back()->with('success', 'Candidate approved successfully.');
    }
    

    public function rejectCandidate(Candidate $candidate)
    {
        $testAttempt = $candidate->tests()
            ->withPivot('completed_at', 'score') // Specify pivot columns explicitly
            ->latest()
            ->first();
    
        if ($testAttempt) {
            Report::updateOrCreate( // Prevent duplicates
                [
                    'candidate_id' => $candidate->id,
                    'test_id' => $testAttempt->id,
                ],
                [
                    'score' => $testAttempt->pivot->score,
                    'completion_status' => 'rejected',
                    'date_completed' => $testAttempt->pivot->completed_at
                ]
            );
        }
    
        return redirect()->back()->with('success', 'Candidate rejected successfully.');
    }
}