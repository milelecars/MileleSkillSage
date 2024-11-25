<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Candidate;
use App\Models\Test;
use App\Models\CandidateFlag;
use App\Models\FlagType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportPDFController extends Controller
{
    private function getClientIP()
    {
        $ipaddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
    
        return $ipaddress;
    }
    
    private function getLocationFromIP($ipAddress)
    {
        $cacheKey = 'ip_location_' . $ipAddress;
        
        return Cache::remember($cacheKey, now()->addDays(1), function () use ($ipAddress) {
            try {
                // Get the real client IP
                $realIP = $this->getClientIP();
                Log::info('Real Client IP:', ['ip' => $realIP]);
    
                $response = Http::get("http://ip-api.com/json/{$realIP}");
                $data = $response->json();
                
                Log::info('IP API Response:', $data);
    
                if ($response->successful() && ($data['status'] ?? '') === 'success') {
                    return sprintf(
                        '%s (%s)',
                        $data['city'] ?? 'Unknown City',
                        $data['country'] ?? 'Unknown Country'
                    );
                }
            } catch (\Exception $e) {
                Log::error('IP location lookup failed: ' . $e->getMessage());
            }
            
            return 'Location not available';
        });
    }

    private function debugIpHeaders()
    {
        $headers = [
            'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? 'not set',
            'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not set',
            'HTTP_X_FORWARDED' => $_SERVER['HTTP_X_FORWARDED'] ?? 'not set',
            'HTTP_FORWARDED_FOR' => $_SERVER['HTTP_FORWARDED_FOR'] ?? 'not set',
            'HTTP_FORWARDED' => $_SERVER['HTTP_FORWARDED'] ?? 'not set',
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'not set'
        ];
        
        Log::info('All possible IP sources:', $headers);
        return $headers;
    }
    
    private function calculateScore($score, $totalQuestions)
    {
        return $score > 0 ? round(($score / $totalQuestions) * 100, 2) : 0;
    }

    public function generateSimplePDF($candidateId, $testId)
    {
        $this->debugIpHeaders();

        $this->debugIpHeaders();

        $candidate = Candidate::findOrFail($candidateId);
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
            'title' => 'Simple PDF Report',
            'date' => date('Y-m-d'),
            'companyName' => 'Milele Motors',
            'department' => 'Admin & Personal Assistant', 
            'candidateName' => $candidate->name,
            'email' => $candidate->email,
            'overallRating' => $this->calculateScore($candidateTest->score, $totalQuestions),
            'status' => 'Completed on ' . date('M d, Y', strtotime($candidateTest->completed_at)),
            'averageScore' => $this->calculateScore($candidateTest->score, $totalQuestions),
            'weightedScore' => $this->calculateScore($candidateTest->score, $totalQuestions),
            'antiCheat' => $antiCheatData,
            'tests' => [
                [
                    'name' => $test->title,
                    'score' => $this->calculateScore($candidateTest->score, $totalQuestions),
                    'description' => $test->description,
                    'time_spent' => gmdate('H:i:s', strtotime($candidateTest->completed_at) - strtotime($candidateTest->started_at)),
                    'time_limit' => gmdate('H:i:s', $test->duration * 60),
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
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream();
    }
}