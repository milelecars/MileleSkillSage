<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\LivewireComponent;
use App\Models\Test;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

#[LivewireComponent]
class TestTimer extends Component
{
    public $testId;
    public $timeLeft;
    public $endTime;
    public $testStarted = false;
    public $minutes;
    public $seconds;
    private $testController;
    
    // Increase the polling interval to reduce AJAX requests
    #[Polling('10s')]
    public function poll()
    {
        $this->calculateRemainingTime();
    }

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
            $this->minutes = floor($this->timeLeft / 60);
            $this->seconds = $this->timeLeft % 60;
            
            if ($this->timeLeft <= 0) {
                $this->handleTimeUp();
            }
        }
    }

    public function handleTimeUp()
    {
        // Existing timeout handling code
        Log::info('Test timer expired', [
            'test_id' => $this->testId,
            'end_time' => $this->endTime
        ]);

        $candidate = Auth::guard('candidate')->user();
        $test = Test::with([
            'questions' => function ($query) use ($candidate) {
                $query->with([
                    'answers' => function ($subQuery) use ($candidate) {
                        $subQuery->where('candidate_id', $candidate->id);
                    },
                    'choices',
                    'media'
                ]);
            },
            'admin'
        ])->findOrFail($this->testId);
        
        if($test->title == "General Mental Ability (GMA)"){
            $questions = $test->questions()
            ->with(['choices', 'media'])
            ->skip(8)
            ->take(PHP_INT_MAX)
            ->get();
        }else{
            
            $questions = $test->questions;
        }

        Log::info('Loaded test and questions for submission', [
            'test_id' => $this->testId,
            'candidate_id' => $candidate->id,
            'question_count' => $questions->count()
        ]);
        
        $testAttempt = $candidate->tests()
        ->wherePivot('test_id', $this->testId)
        ->first();
        if (!$testAttempt) {
            Log::error('Test attempt not found in TestTimer', ['test_id' => $this->testId, 'candidate_id' => $candidate->id]);
            return redirect()->route('tests.start', ['id' => $this->testId])
                ->with('error', 'Test attempt not found.');
        }

        try {
            $test = Test::findOrFail($this->testId);
            $testController = App::make(TestController::class);
            return $testController->handleTimeout($test);
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
        return view('livewire.test-timer', [
            'minutes' => $this->minutes,
            'seconds' => $this->seconds,
            'timeLeft' => $this->timeLeft
        ]);
    }
}