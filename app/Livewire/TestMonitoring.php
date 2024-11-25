<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CandidateFlag;
use App\Models\FlagType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;



class TestMonitoring extends Component
{
    public $metrics = [];
    public $flags;
    public $testSessionId;

    public function mount($testSessionId)
    {
        $this->testSessionId = $testSessionId;
        $this->flags = FlagType::all();
        
        // Get existing flags
        $existingFlags = CandidateFlag::where([
            'test_id' => $this->testSessionId,
            'candidate_id' => auth()->guard('candidate')->id()
        ])->get();
    
        // Initialize metrics
        foreach ($this->flags as $flagType) {
            $metricKey = lcfirst(str_replace(' ', '', $flagType->name));
            $existingFlag = $existingFlags->firstWhere('flag_type_id', $flagType->id);
            $this->metrics[$metricKey] = $existingFlag ? $existingFlag->occurrences : 0;
        }
    }
    
    public function logSuspiciousBehavior($flagType)
    {
        // Find the flag type by name
        $flagTypeModel = FlagType::where('name', $flagType)->first();
        
        if ($flagTypeModel) {
            $candidateFlag = CandidateFlag::firstOrCreate(
                [
                    'test_id' => $this->testSessionId,
                    'flag_type_id' => $flagTypeModel->id,
                    'candidate_id' => auth()->guard('candidate')->id()
                ],
                [
                    'occurrences' => 0,
                    'is_flagged' => false
                ]
            );

            $candidateFlag->increment('occurrences');
            
            Log::info("Incremented violation count for $flagType", [
                'newCount' => $candidateFlag->fresh()->occurrences
            ]);

            $metricKey = lcfirst(str_replace(' ', '', $flagType));
            if (isset($this->metrics[$metricKey])) {
                $this->metrics[$metricKey]++;
        
                if ($this->metrics[$metricKey] > $flagTypeModel->threshold) {
                    $candidateFlag->update(['is_flagged' => true]);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.test-monitoring');
    }
}