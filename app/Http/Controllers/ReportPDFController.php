<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Candidate;
use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportPDFController extends Controller
{
    public function generateSimplePDF($candidateId, $testId)
    {
        // Fetch candidate data
        $candidate = Candidate::findOrFail($candidateId);

        // Fetch test data
        $test = Test::findOrFail($testId);

        // Fetch data from the candidate_test pivot table
        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->first();

        if (!$candidateTest) {
            abort(404, 'Test data for the candidate not found.');
        }

        // Prepare data for the PDF
        $data = [
            'title' => 'Skill Test Report',
            'date' => now()->format('Y-m-d'),
            'companyName' => 'Milele Motors',
            'department' => 'Admin & Personal Assistant', 
            'candidateName' => $candidate->name,
            'email' => $candidate->email,
            'overallRating' => $candidateTest->score,
            'status' => 'Completed: ' . date('M d, Y', strtotime($candidateTest->completed_at)),
            'averageScore' => $candidateTest->score, 
            'weightedScore' => $candidateTest->score, 

            // Static weights (example), replace with dynamic data if available
            'weights' => [
                [
                    'name' => 'Administrative Assistant',
                    'weight' => 3,
                    'impact' => 33,
                ],
                [
                    'name' => 'Executive Assistant',
                    'weight' => 5,
                    'impact' => 55,
                ],
                [
                    'name' => 'Negotiation',
                    'weight' => 1,
                    'impact' => 11,
                ],
            ],

            // Example: Fetch test-related questions and results
            'tests' => [
                [
                    'name' => $test->title,
                    'score' => $candidateTest->score,
                    'description' => $test->description,
                    'time_spent' => gmdate('H:i:s', strtotime($candidateTest->completed_at) - strtotime($candidateTest->started_at)),
                    'time_limit' => gmdate('H:i:s', $test->duration * 60),
                    'skills' => [
                        // Example data, replace with actual skill calculations
                        [
                            'name' => 'Controlling and driving the discussion',
                            'correct' => 35,
                            'incorrect' => 65,
                            'unanswered' => 0,
                        ],
                        [
                            'name' => 'Influencing the counterparty',
                            'correct' => 25,
                            'incorrect' => 75,
                            'unanswered' => 0,
                        ],
                    ],
                ],
            ],

            // Dynamic anti-cheat data
            'antiCheat' => [
                ['label' => 'Device used', 'value' => 'Desktop'],
                ['label' => 'Location', 'value' => 'Rawalpindi (Punjab), PK'],
                ['label' => 'Filled out only once from IP address?', 'value' => 'Yes'],
                ['label' => 'Webcam enabled?', 'value' => 'Yes'],
                ['label' => 'Full-screen mode always active?', 'value' => 'Yes'],
                ['label' => 'Mouse always in assessment window?', 'value' => 'Yes'],
                ['label' => 'Tab Switches', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Window Blurs', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Mouse Exits', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Copy/Cut Attempts', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Right Clicks', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Keyboard Shortcuts', 'value' => '0', 'flagged' => 'No'],
                ['label' => 'Total Warnings', 'value' => '0', 'flagged' => 'No'],
            ],
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.candidate-report', $data);

        $pdf->getDomPDF()->set_option('defaultFont', 'figtree');
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream();
    }
}
