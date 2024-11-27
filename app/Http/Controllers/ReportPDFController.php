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
            abort(404, 'Test data for the candidate not found.');
        }

        // If report exists and file exists in storage, just stream it
        if ($candidateTest->report_path && Storage::disk('public')->exists($candidateTest->report_path)) {
            Log::info("report exists");
            return response()->file(Storage::disk('public')->path($candidateTest->report_path));
        }

        // If report doesn't exist, generate and stream it
        $fullPath = $this->testReportService->generatePDF($candidateId, $testId);
        return response()->file(Storage::disk('public')->path($fullPath));
    }


}
