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

    protected $listeners = [
        'logSuspiciousBehavior',
        'refreshMetrics' => '$refresh'
    ];

    public function mount($testSessionId)
    {
        try {
            $this->testSessionId = $testSessionId;
            $this->flags = FlagType::all();
            $this->flagTypes = $this->flags->pluck('id', 'name')->all();
            
            // Load initial metrics from database
            $this->loadMetricsFromDatabase();

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

    protected function loadMetricsFromDatabase()
    {
        // Initialize metrics with zeros
        foreach ($this->flags as $flagType) {
            $metricKey = $this->getMetricKey($flagType->name);
            $this->metrics[$metricKey] = 0;
        }

        // Load actual values from database
        $candidateFlags = CandidateFlag::where([
            'test_id' => $this->testSessionId,
            'candidate_id' => auth()->guard('candidate')->id()
        ])->get();

        foreach ($candidateFlags as $flag) {
            $flagType = $this->flags->find($flag->flag_type_id);
            if ($flagType) {
                $metricKey = $this->getMetricKey($flagType->name);
                $this->metrics[$metricKey] = $flag->occurrences;
            }
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

            // Case-insensitive search for flag type
            $flagTypeModel = $this->flags->first(function($flag) use ($flagType) {
                return strcasecmp($flag->name, $flagType) === 0;
            });
            
            if (!$flagTypeModel) {
                Log::warning('Unknown flag type', [
                    'flagType' => $flagType,
                    'availableTypes' => $this->flags->pluck('name')->toArray()
                ]);
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
            
            // Reload metrics from database after update
            $this->loadMetricsFromDatabase();
            
            Log::info("Violation logged", [
                'flagType' => $flagType,
                'flagTypeId' => $flagTypeModel->id,
                'testId' => $this->testSessionId,
                'candidateId' => $candidateId,
                'occurrences' => $candidateFlag->occurrences
            ]);
            
            if ($candidateFlag->occurrences > $flagTypeModel->threshold) {
                $candidateFlag->update(['is_flagged' => true]);
            }

        } catch (Exception $e) {
            Log::error('Error logging suspicious behavior', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'flagType' => $flagType ?? 'unknown'
            ]);
        }
    }

    public function render()
    {
        // Reload metrics before every render
        $this->loadMetricsFromDatabase();
        return view('livewire.test-monitoring');
    }
}