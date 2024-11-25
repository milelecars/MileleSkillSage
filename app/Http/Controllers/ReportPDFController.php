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

        $candidate = Candidate::findOrFail($candidateId);
        $test = Test::findOrFail($testId);

        $totalQuestions = DB::table('questions')
        ->where('test_id', $testId)
        ->count();

        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->first();

        if (!$candidateTest) {
            abort(404, 'Test data for the candidate not found.');
        }

        $ip = $candidateTest->ip_address;
        Log::info('Looking up IP:', ['ip' => $ip]);

        $location = $ip ? $this->getLocationFromIP($ip) : 'No IP recorded';
        Log::info('Location result:', ['location' => $location]);

        // Fetch anti-cheat data
        $candidateFlags = CandidateFlag::where([
            'test_id' => $testId,
            'candidate_id' => $candidateId
        ])
        ->join('flag_types', 'candidate_flags.flag_type_id', '=', 'flag_types.id')
        ->select('flag_types.name', 'candidate_flags.occurrences', 'candidate_flags.is_flagged')
        ->get();

        $antiCheatData = [
            ['label' => 'Device used', 'value' => 'Desktop'],
            ['label' => 'Location', 'value' => $location],
            ['label' => 'IP Address', 'value' => $ip ?? 'Not available'],
            ['label' => 'Filled out only once from IP address?', 'value' => 'Yes'],
            ['label' => 'Webcam enabled?', 'value' => 'Yes'],
            ['label' => 'Full-screen mode always active?', 'value' => 'Yes'],
            ['label' => 'Mouse always in assessment window?', 'value' => 'Yes'],
        ];

        
        $tabSwitches = $candidateFlags->first(function ($flag) {
            return $flag->name === 'Tab Switches';
        });
        if ($tabSwitches && $tabSwitches->occurrences > 0) {
            $antiCheatData[5]['value'] = 'No';
        }

        // Add violation counts
        foreach ($candidateFlags as $flag) {
            $antiCheatData[] = [
                'label' => $flag->name,
                'value' => (string)$flag->occurrences,
                'flagged' => $flag->is_flagged ? 'Yes' : 'No'
            ];
        }

        // // Add total warnings
        // $totalWarnings = $candidateFlags->where('is_flagged', true)->count();
        // $antiCheatData[] = [
        //     'label' => 'Total Warnings',
        //     'value' => (string)$totalWarnings,
        //     'flagged' => $totalWarnings > 0 ? 'Yes' : 'No'
        // ];

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