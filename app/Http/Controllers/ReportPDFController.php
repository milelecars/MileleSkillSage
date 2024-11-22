<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportPDFController extends Controller
{
    public function generateSimplePDF()
    {
        $data = [
            'title' => 'Simple PDF Report',
            'date' => date('Y-m-d'),
            'companyName' => 'Milele Motors',
            'department' => 'Admin & Personal Assistant',
            'candidateName' => 'Abdullah Nawab',
            'email' => 'abdullahnawab654@gmail.com',
            'overallRating' => '0.0',
            'status' => 'Completed: Sep 10, 2024',
            'averageScore' => 49,
            'weightedScore' => 59,
            
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
            
            'tests' => [
                [
                    'name' => 'Negotiation',
                    'score' => 20,
                    'description' => 'This Negotiation test evaluates a candidate\'s ability to negotiate in a business context. This screening test will help you hire employees who can negotiate for your interests in a variety of contexts.',
                    'time_spent' => '00:10:00',
                    'time_limit' => '00:10:00',
                    'skills' => [
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
                        [
                            'name' => 'Leveraging the psychology of the counterparty',
                            'correct' => 20,
                            'incorrect' => 30,
                            'unanswered' => 50,
                        ],
                        [
                            'name' => 'Using emotional intelligence',
                            'correct' => 0,
                            'incorrect' => 0,
                            'unanswered' => 100,
                        ],
                    ],
                ],
            ],
            
            'antiCheat' => [
                ['label' => 'Device used', 'value' => 'Desktop'],
                ['label' => 'Location', 'value' => 'Rawalpindi (Punjab), PK'],
                ['label' => 'Filled out only once from IP address?', 'value' => 'Yes'],
                ['label' => 'Webcam enabled?', 'value' => 'Yes'],
                ['label' => 'Full-screen mode always active?', 'value' => 'Yes'],
                ['label' => 'Mouse always in assessment window?', 'value' => 'Yes'],
            ],
        ];

        $pdf = Pdf::loadView('reports.candidate-report', $data);

        $pdf->getDomPDF()->set_option('defaultFont', 'figtree');
        $pdf->setPaper('A4', 'portrait');        
        
        return $pdf->stream();
    }
}