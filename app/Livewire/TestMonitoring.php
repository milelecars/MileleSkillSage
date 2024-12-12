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
            
            // Get existing flags with eager loading
            $existingFlags = CandidateFlag::where([
                'test_id' => $this->testSessionId,
                'candidate_id' => auth()->guard('candidate')->id()
            ])->get();
        
            // Initialize metrics
            foreach ($this->flags as $flagType) {
                $metricKey = $this->getMetricKey($flagType->name);
                $existingFlag = $existingFlags->firstWhere('flag_type_id', $flagType->id);
                $this->metrics[$metricKey] = $existingFlag ? $existingFlag->occurrences : 0;
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
                Log::warning('Invalid test session or unauthorized access', [
                    'testSessionId' => $this->testSessionId,
                    'isAuthenticated' => auth()->guard('candidate')->check()
                ]);
                return;
            }

            // Find the flag type by name
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
            $newCount = $candidateFlag->fresh()->occurrences;
            
            Log::info("Violation logged", [
                'flagType' => $flagType,
                'testId' => $this->testSessionId,
                'candidateId' => $candidateId,
                'occurrences' => $newCount
            ]);

            $metricKey = $this->getMetricKey($flagType);
            if (isset($this->metrics[$metricKey])) {
                $this->metrics[$metricKey] = $newCount;
        
                if ($this->metrics[$metricKey] > $flagTypeModel->threshold) {
                    $candidateFlag->update(['is_flagged' => true]);
                    Log::info("Threshold exceeded", [
                        'flagType' => $flagType,
                        'threshold' => $flagTypeModel->threshold,
                        'currentValue' => $this->metrics[$metricKey]
                    ]);
                }
            }
            
            // Emit event for real-time updates
            $this->dispatch('metricUpdated', [
                'metric' => $metricKey,
                'value' => $this->metrics[$metricKey]
            ]);

        } catch (Exception $e) {
            Log::error('Error logging suspicious behavior', [
                'error' => $e->getMessage(),
                'flagType' => $flagType,
                'testSessionId' => $this->testSessionId
            ]);
            throw $e;
        }
    }

    public function render()
    {
        try {
            return view('livewire.test-monitoring');
        } catch (Exception $e) {
            Log::error('Error rendering TestMonitoring component', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function ensureAuthenticated()
    {
        if (!auth()->guard('candidate')->check()) {
            throw new Exception('Unauthorized access');
        }
    }
}