<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CandidateFlag;
use App\Models\FlagType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class TestMonitoring extends Component
{
    public $metrics = [];
    public $flags;
    public $testSessionId;
    protected $flagTypes = null;

    public function mount($testSessionId)
    {
        try {
            $this->testSessionId = $testSessionId;
            $this->flags = FlagType::all();
            $this->flagTypes = $this->flags->pluck('id', 'name')->all();
            
            // Initialize metrics
            foreach ($this->flags as $flagType) {
                $metricKey = $this->getMetricKey($flagType->name);
                $this->metrics[$metricKey] = 0;
            }

            Log::info('TestMonitoring initialized', [
                'testSessionId' => $this->testSessionId,
                'candidateId' => auth()->guard('candidate')->id(),
                'metricsInitialized' => array_keys($this->metrics)
            ]);
        } catch (Exception $e) {
            Log::error('Error initializing TestMonitoring', [
                'error' => $e->getMessage(),
                'testSessionId' => $testSessionId
            ]);
            throw $e;
        }
    }
    
    protected function getMetricKey($flagTypeName)
    {
        return lcfirst(str_replace(' ', '', $flagTypeName));
    }
    
    public function logSuspiciousBehavior($flagType)
    {
        try {
            if (!$this->testSessionId || !auth()->guard('candidate')->check()) {
                Log::warning('Invalid test session or unauthorized access');
                return;
            }

            $flagTypeModel = $this->flags->firstWhere('name', $flagType);
            
            if (!$flagTypeModel) {
                Log::warning('Unknown flag type', ['flagType' => $flagType]);
                return;
            }

            $candidateId = auth()->guard('candidate')->id();
            
            $candidateFlag = CandidateFlag::firstOrCreate(
                [
                    'test_id' => $this->testSessionId,
                    'flag_type_id' => $flagTypeModel->id,
                    'candidate_id' => $candidateId
                ],
                [
                    'occurrences' => 0,
                    'is_flagged' => false
                ]
            );

            $candidateFlag->increment('occurrences');
            
            Log::info("Violation logged", [
                'flagType' => $flagType,
                'testId' => $this->testSessionId,
                'candidateId' => $candidateId
            ]);
            
            if ($candidateFlag->occurrences > $flagTypeModel->threshold) {
                $candidateFlag->update(['is_flagged' => true]);
            }

        } catch (Exception $e) {
            Log::error('Error logging suspicious behavior', [
                'error' => $e->getMessage(),
                'flagType' => $flagType
            ]);
        }
    }

    public function render()
    {
        return view('livewire.test-monitoring');
    }
}