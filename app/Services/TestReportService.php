<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Candidate;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Test;
use App\Models\CandidateFlag;
use App\Models\FlagType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\TestReportService;



class TestReportService
{
    private $apiKey; 
    private $cache;
    private $logger;
    
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    public function generatePDF($candidateId, $testId)
    {
        $this->debugIpHeaders();
    
        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->first();
        
        if (!$candidateTest) {
            abort(404, 'Test data for the candidate not found.');
        }

        $candidate = Candidate::findOrFail($candidateId);
        $test = Test::findOrFail($testId);

        // Fetch only LSQ questions that are red-flagged
        $redFlaggedQuestions = Question::where('test_id', $testId)
            ->where('question_type', 'LSQ')
            ->where('red_flag', true)
            ->get();

        $answerMeanings = [
            1 => 'Strongly Disagree',
            2 => 'Disagree',
            3 => 'Neutral',
            4 => 'Agree',
            5 => 'Strongly Agree',
        ];
            
        // Retrieve LSQ Answers for the red-flagged questions
        $redFlaggedAnswers = Answer::where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->whereIn('question_id', $redFlaggedQuestions->pluck('id'))
            ->get()
            ->map(function ($answer) use ($answerMeanings) {
            return [
                'question_id' => $answer->question_id,
                'answer_value' => $answer->answer_text,
                'meaning' => $answerMeanings[$answer->answer_text] ?? 'No answer provided',
            ];
        });

        // **Group by Category**
        $groupedQuestions = $redFlaggedQuestions->groupBy('category');
        Log::info('hi cat', [$groupedQuestions]);
    
        // Move this query up before using $candidateFlags
        $candidateFlags = CandidateFlag::where([
            'test_id' => $testId,
            'candidate_id' => $candidateId
        ])
        ->join('flag_types', 'candidate_flags.flag_type_id', '=', 'flag_types.id')
        ->select('flag_types.name', 'candidate_flags.occurrences', 'candidate_flags.is_flagged')
        ->get();

        if($test->title == "General Mental Ability (GMA)"){
            $totalQuestions = DB::table('questions')
                ->where('test_id', $testId)
                ->count() - 8;
        }else{
            
            $totalQuestions = DB::table('questions')
                ->where('test_id', $testId)
                ->count();
        }

        $hasMCQ = $totalQuestions->contains('question_type', 'MCQ');
        $hasLSQ = $totalQuestions->contains('question_type', 'LSQ');

        $ip = $candidateTest->ip_address;
        Log::info('Looking up IP:', ['ip' => $ip]);
    
        $locationData = $ip ? $this->getLocationFromIP($ip) : ['formatted_address' => 'No IP recorded'];
        $locationString = is_array($locationData) ? ($locationData['formatted_address'] ?? 'Location not available') : 'Location not available';
        Log::info('Location result:', ['location' => $locationString]);

        $antiCheatData = [
            ['label' => 'Device used', 'value' => 'Desktop'],
            ['label' => 'Location', 'value' => $locationString], 
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

        foreach ($candidateFlags as $flag) {
            $antiCheatData[] = [
                'label' => $flag->name,
                'value' => (string)$flag->occurrences,
                'flagged' => $flag->is_flagged ? 'Yes' : 'No'
            ];
        }

        $testAttempt = $candidate->tests()
        ->wherePivot('test_id', $testId)
        ->first();
        if (!$testAttempt) {
            Log::error('Test attempt not found in TestReportService', ['test_id' => $testId, 'candidate_id' => $candidate->id]);
            return redirect()->route('tests.start', ['id' => $testId])
                ->with('error', 'Test attempt not found.');
        }

        $data = [
            'title' => 'Skill Test Report',
            'date' => now()->format('Y-m-d'),
            'companyName' => 'Milele Motors',
            'department' => 'HR Department', 
            'candidateName' => $candidate->name,
            'email' => $candidate->email,
            'overallRating' => $testAttempt->pivot->score,
            'status' => 'Completed on ' . date('M d, Y', strtotime($candidateTest->completed_at)),
            'score' => $testAttempt->pivot->score,
            'weightedScore' => $testAttempt->pivot->score,
            'antiCheat' => $antiCheatData,
            'redFlaggedAnswers' => $redFlaggedAnswers ?? [],
            'redFlaggedQuestions' => $redFlaggedQuestions ?? [],
            'groupedQuestions' => $groupedQuestions  ?? [],
            'hasMCQ' => $hasMCQ,
            'hasLSQ' => $hasLSQ,
            
        ];

        $fileName = "report_candidate{$candidateId}_test{$testId}_" . time() . '.pdf';
        $folderPath = "reports";

        if(!Storage::disk('public')->exists($folderPath)){
            Storage::disk('public')->makeDirectory($folderPath);
        }

        $fullPath = $folderPath . '/' . $fileName;
        Log::info("full path is", ['path' => $fullPath]);

        $pdf = Pdf::loadView('reports.candidate-report', $data);
        $pdf->getDomPDF()->set_option('defaultFont', 'figtree');
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->setPaper('A4', 'portrait');

        Storage::disk('public')->put($fullPath, $pdf->output());

        DB::table('candidate_test')
            ->where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->update(['report_path' => $fullPath]);

        return $fullPath;
    }

    public function getClientIP()
    {
        $ipHeaders = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipHeaders as $header) {
            if (isset($_SERVER[$header])) {
                // Handle X-Forwarded-For header which may contain multiple IPs
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $_SERVER[$header]);
                    return trim($ips[0]); // Return the first IP in the list
                }
                return $_SERVER[$header];
            }
        }
        
        return 'UNKNOWN';
    }
    
    public function getLocationFromIP($ipAddress)
    {
        $cacheKey = 'ip_location_' . $ipAddress;
        
        return Cache::remember($cacheKey, now()->addDays(1), function () use ($ipAddress) {
            try {
                // Get the real client IP
                $realIP = $this->getClientIP();
                Log::info('Real Client IP:', ['ip' => $realIP]);
                
                // Use ip-api.com's extended endpoint for more data
                $endpoint = "http://ip-api.com/json/{$realIP}";
                $query = http_build_query([
                    'fields' => 'status,message,continent,country,countryCode,region,regionName,city,zip,lat,lon,timezone,isp,org,as,mobile,proxy,hosting'
                ]);
                
                $response = Http::get($endpoint . '?' . $query);
                $data = $response->json();
                
                Log::info('IP API Response:', $data);

                if ($response->successful() && ($data['status'] ?? '') === 'success') {
                    return [
                        'formatted_address' => sprintf(
                            '%s, %s, %s',
                            $data['city'] ?? 'Unknown City',
                            $data['regionName'] ?? 'Unknown Region',
                            $data['country'] ?? 'Unknown Country'
                        ),
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'zip' => $data['zip'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'isp' => $data['isp'] ?? null,
                        'organization' => $data['org'] ?? null,
                        'is_proxy' => $data['proxy'] ?? false,
                        'is_mobile' => $data['mobile'] ?? false,
                        'is_hosting' => $data['hosting'] ?? false
                    ];
                }
                
                throw new \Exception('Failed to get location data: ' . ($data['message'] ?? 'Unknown error'));
            } catch (\Exception $e) {
                Log::error('IP location lookup failed: ' . $e->getMessage(), [
                    'ip' => $ipAddress,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return [
                    'formatted_address' => 'Location not available',
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    public function debugIpHeaders()
    {
        $headers = [
            'HTTP_CLIENT_IP' => $_SERVER['HTTP_CLIENT_IP'] ?? 'not set',
            'HTTP_X_FORWARDED_FOR' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'not set',
            'HTTP_X_FORWARDED' => $_SERVER['HTTP_X_FORWARDED'] ?? 'not set',
            'HTTP_FORWARDED_FOR' => $_SERVER['HTTP_FORWARDED_FOR'] ?? 'not set',
            'HTTP_FORWARDED' => $_SERVER['HTTP_FORWARDED'] ?? 'not set',
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'not set',
            'RESOLVED_IP' => $this->getClientIP()
        ];
        
        Log::info('IP Headers Debug:', $headers);
        return $headers;
    }

    public function validateIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
    
}