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
        $questions = [];
        if ($test->questions_file_path) {
            $filePath = storage_path('app/public/' . $test->questions_file_path);
            $questions = Excel::toArray(new QuestionsImport($test), $filePath);
            $questions = $questions[0] ?? [];
        }
        return $questions;
    }

    private function getQuestionsCount($test)
    {
        return count($this->getQuestions($test));
    }

    public function manageCandidates()
    {
        $candidates = Candidate::with(['tests' => function($query) {
            $query->select('tests.id', 'name', 'questions_file_path');
        }])
        ->select('id', 'name', 'email', 'test_name', 'test_started_at', 
                'test_completed_at', 'test_score')
        ->latest('test_completed_at')
        ->paginate(10);

        
        foreach ($candidates as $candidate) {
            if ($test = $candidate->tests->first()) {
                $candidate->total_questions = $this->getQuestionsCount($test);
            }
        }

        $stats = [
            'totalCandidates' => Candidate::count(),
            'completedTests' => Candidate::whereNotNull('test_completed_at')->count(),
            'activeTests' => Test::count()
        ];

        return view('admin.manage-candidates', array_merge(compact('candidates'), $stats));
    }

    public function candidateResult(Candidate $candidate)
    {
        $test = $candidate->tests()->first();
        if (!$test) {
            return redirect()->back()->with('error', 'No test found for this candidate.');
        }

        $questions = $this->getQuestions($test);
        $totalQuestions = count($questions);

        return view('admin.candidate-result', [
            'candidate' => $candidate,
            'test' => $test,
            'testAttempt' => $test->pivot,
            'totalQuestions' => $totalQuestions,
            'percentage' => $totalQuestions > 0 ? ($candidate->test_score / $totalQuestions * 100) : 0
        ]);
    }

    public function approveCandidate(Candidate $candidate)
    {
        $candidate->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Candidate approved successfully');
    }

    public function rejectCandidate(Candidate $candidate)
    {
        $candidate->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Candidate rejected successfully');
    }
}