<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\CandidateTestScreenshot;
use Illuminate\Support\Str;

class ScreenshotController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Get current candidate and test IDs from session
            $candidateId = $request->session()->get('candidate_id');
            $testId = $request->session()->get('test_id');

            $candidateTest = \DB::table('candidate_test')->where('candidate_id', $candidateId)->where('test_id', $testId)->first();

            if (!$candidateTest) {
                throw new \Exception('No active test session found');
            }

            // Validate input
            $request->validate([
                'screenshot' => 'required|string',
                'timestamp' => 'required|date'
            ]);

            // Remove the data:image/jpeg;base64 prefix
            $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $request->screenshot);
            $imageData = base64_decode($base64Image);

            $filename = Str::uuid() . '.jpg';
            $folderPath = "screenshots/{$candidateTest->id}";

            if (!Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath);
            }

            $fullPath = $folderPath .'/'. $fileName; 
            Storage::disk('public')->put($fullPath, $imageData);

            // Save record to database
            CandidateTestScreenshot::create([
                'candidate_test_id' => $candidateTest->id,
                'screenshot_path' => $fullPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Screenshot saved successfully',
                'path' => $fullPath
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save screenshot: ' . $e->getMessage()
            ], 500);
        }
    }
}