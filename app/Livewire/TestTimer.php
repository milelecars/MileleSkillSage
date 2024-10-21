<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\LivewireComponent;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

#[LivewireComponent]
class TestTimer extends Component
{
    public $testId;
    public $timeLeft;
    public $endTime;
    public $testStarted = false;

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
                $this->redirectToResults();
            }
        }
    }

    public function redirectToResults()
    {
        session()->forget('test_session');
        
        return Redirect::route('tests.result', ['id' => $this->testId]);
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