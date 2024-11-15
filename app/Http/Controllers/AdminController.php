<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Test;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => Candidate::whereNotNull('test_completed_at')->count(),
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
        
        // TODO:understand this
       $candidates = Candidate::with([
           'tests' => function($query) {
               $query->select('tests.id', 'title', 'description')
                    ->withPivot('started_at', 'completed_at', 'score');
           },
           'tests.questions',
           'reports'
       ])
       ->select('id', 'name', 'email')
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
    $test = $candidate->tests()->with(['questions.choices', 'questions.media'])->first();
    
    if (!$test) {
        return redirect()->back()->with('error', 'No test found for this candidate.');
    }

    $answers = Answer::where('candidate_id', $candidate->id)
        ->whereHas('question', function($query) use ($test) {
            $query->where('test_id', $test->id);
        })->get();

    $report = Report::where('candidate_id', $candidate->id)
        ->where('test_id', $test->id)
        ->first();

    return view('admin.candidate-result', [
        'candidate' => $candidate,
        'test' => $test,
        'testAttempt' => $test->pivot,
        'answers' => $answers,
        'report' => $report,
        'totalQuestions' => $test->questions->count(),
        'percentage' => $test->questions->count() > 0 ? 
            ($test->pivot->score / $test->questions->count() * 100) : 0
    ]);
    }

    public function approveCandidate(Candidate $candidate)
    {
    $testAttempt = $candidate->tests()->latest()->first();
    
    if ($testAttempt) {
        Report::create([
            'candidate_id' => $candidate->id,
            'test_id' => $testAttempt->id,
            'score' => $testAttempt->pivot->score,
            'completion_status' => 'approved',
            'date_completed' => $testAttempt->pivot->completed_at
        ]);
    }

    return redirect()->back()->with('success', 'Candidate approved successfully');
    }

    public function rejectCandidate(Candidate $candidate)
    {
    $testAttempt = $candidate->tests()->latest()->first();
    
    if ($testAttempt) {
        Report::create([
            'candidate_id' => $candidate->id,
            'test_id' => $testAttempt->id,
            'score' => $testAttempt->pivot->score,
            'completion_status' => 'rejected',
            'date_completed' => $testAttempt->pivot->completed_at
        ]);
    }

    return redirect()->back()->with('success', 'Candidate rejected successfully');
    }
}