<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\TestReportService;



class ReportPDFController extends Controller
{
    protected $testReportService;

    public function __construct(TestReportService $testReportService)
    {
        $this->testReportService = $testReportService;
    }

    public function streamPDF($candidateId, $testId)
    {
        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $candidateId)
            ->where('test_id', $testId)
            ->first();
    
        if (!$candidateTest) {
            return response()->json(['error' => 'Test data for the candidate not found.'], 404);
        }
    
        // If report exists and file exists in storage, just stream it
        if ($candidateTest->report_path && Storage::disk('public')->exists($candidateTest->report_path)) {
            Log::info("Report exists");
            return response()->file(Storage::disk('public')->path($candidateTest->report_path));
        }
    
        try {
            // If report doesn't exist, generate and stream it
            $fullPath = $this->testReportService->generatePDF($candidateId, $testId);
    
            if (!$fullPath || !Storage::disk('public')->exists($fullPath)) {
                throw new \Exception("Generated report file not found.");
            }
    
            return response()->file(Storage::disk('public')->path($fullPath));
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF: " . $e->getMessage());
            return response()->json(['error' => 'No report available at the moment. Please try again later.'], 500);
        }
    }
    


}
