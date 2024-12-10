<?php

namespace App\Http\Controllers;

use App\Models\CandidateFlag;
use App\Models\FlagType;
use Illuminate\Http\Request;

class FlagController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'candidateId' => 'required|exists:candidates,id',
            'testId' => 'required|exists:tests,id',
            'flagType' => 'required|string',
            'occurrences' => 'required|integer',
            'isFlagged' => 'required|boolean'
        ]);

        // Find flag type
        $flagType = FlagType::where('name', $validatedData['flagType'])->first();

        if (!$flagType) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Flag type not found'
            ], 404);
        }

        // Create or update candidate flag
        $candidateFlag = CandidateFlag::updateOrCreate(
            [
                'candidate_id' => $validatedData['candidateId'],
                'test_id' => $validatedData['testId'],
                'flag_type_id' => $flagType->id
            ],
            [
                'occurrences' => $validatedData['occurrences'],
                'is_flagged' => $validatedData['isFlagged']
            ]
        );

        return response()->json([
            'status' => 'success', 
            'message' => 'Candidate flag recorded',
            'flag' => $candidateFlag
        ]);
    }

    private function getThresholdForFlagType($flagType)
    {
        return Cache::remember("flag_type_threshold_{$flagType}", now()->addHours(24), function () use ($flagType) {
            $flagTypeRecord = FlagType::where('name', $flagType)->first();
            return $flagTypeRecord ? $flagTypeRecord->threshold : 0;
        });
    }
}