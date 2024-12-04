<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\LivewireComponent;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\App;

#[LivewireComponent]
class TestTimer extends Component
{
    public $testId;
    public $timeLeft;
    public $endTime;
    public $testStarted = false;
    private $testController;

    public function mount($testId)
    {
        $this->testId = $testId;
        $this->initializeTimer();
    }

    public function initializeTimer()
    {
        $testSession = session('test_session');
        if ($testSession && $testSession['test_id'] == $this->testId) {
            $this->endTime = Carbon::parse($testSession['end_time']);
            $this->testStarted = true;
            $this->calculateRemainingTime();
        }
    }

    public function calculateRemainingTime()
    {
        if ($this->endTime) {
            $this->timeLeft = max(0, now()->diffInSeconds($this->endTime, false));
            
            if ($this->timeLeft <= 0) {
                $this->handleTimeUp();
            }
        }
    }

    public function handleTimeUp()
    {
        Log::info('Test timer expired', [
            'test_id' => $this->testId,
            'end_time' => $this->endTime
        ]);

        try {
            $test = Test::findOrFail($this->testId);
            $testController = App::make(TestController::class);
            return $testController->handleExpiredTest($test);
        } catch (\Exception $e) {
            Log::error('Error handling expired test time', [
                'test_id' => $this->testId,
                'error' => $e->getMessage()
            ]);
            
            session()->forget('test_session');
            return redirect()->route('tests.result', ['id' => $this->testId])
                ->with('warning', 'Test time has expired.');
        }
    }

    public function render()
    {
        $this->calculateRemainingTime();
        
        return view('livewire.test-timer', [
            'minutes' => floor($this->timeLeft / 60),
            'seconds' => $this->timeLeft % 60
        ]);
    }

    #[Polling('1s')]
    public function poll()
    {
        $this->calculateRemainingTime();
    }
}