<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Test;
use Carbon\Carbon;

class TestPlayer extends Component
{
    public $test;
    public $candidate;
    public $questions;
    public $currentIndex = 0;
    public $selectedAnswer = null;
    public $lsqValue = 3;

    protected $rules = [
        'selectedAnswer' => 'nullable|exists:question_choices,id',
        'lsqValue' => 'nullable|integer|min:1|max:5',
    ];

    public function mount($test, $candidate, $questions, $currentIndex)
    {
        $this->test = $test;
        $this->candidate = $candidate;
        $this->questions = $questions;
        $this->currentIndex = $currentIndex;

        $sessionData = session('test_session');
        $savedAnswer = $sessionData['answers'][$this->currentIndex] ?? null;
        $this->selectedAnswer = is_numeric($savedAnswer) ? $savedAnswer : null;
        $this->lsqValue = is_numeric($savedAnswer) ? $savedAnswer : 3;
    }

    public function submitAndNext()
    {
        $session = session('test_session');
    
        // ✅ Prevent accessing an invalid index
        if (!isset($this->questions[$this->currentIndex])) {
            logger()->error('Invalid currentIndex in Livewire', [
                'index' => $this->currentIndex,
                'total' => count($this->questions)
            ]);
            return;
        }
    
        $question = $this->questions[$this->currentIndex];
    
        // ✅ Save MCQ Answer
        if ($question->question_type === 'MCQ' && $this->selectedAnswer) {
            $answerText = $question->choices->firstWhere('id', $this->selectedAnswer)->choice_text ?? null;
            Answer::updateOrCreate([
                'candidate_id' => $this->candidate->id,
                'test_id' => $this->test->id,
                'question_id' => $question->id,
            ], [
                'answer_text' => $answerText,
            ]);
            $session['answers'][$this->currentIndex] = $this->selectedAnswer;
        }
    
        // ✅ Save LSQ Answer
        if ($question->question_type === 'LSQ') {
            Answer::updateOrCreate([
                'candidate_id' => $this->candidate->id,
                'test_id' => $this->test->id,
                'question_id' => $question->id,
            ], [
                'answer_text' => $this->lsqValue,
            ]);
            $session['answers'][$this->currentIndex] = $this->lsqValue;
        }
    
        // ✅ Move index forward AFTER saving
        $this->currentIndex++;
    
        // ✅ Redirect to GET /submit if we've reached the end
        if ($this->currentIndex >= count($this->questions)) {
            return redirect()->route('tests.submit', ['id' => $this->test->id]);
        }
    
        $session['current_question'] = $this->currentIndex;
        session(['test_session' => $session]);
    
        $this->reset('selectedAnswer', 'lsqValue');
    }

    protected $listeners = ['timeExpired' => 'handleTimeExpiry'];

    public function handleTimeExpiry()
    {
        logger()->info('[Livewire] Timer expired, handleTimeExpiry triggered', [
            'candidate_id' => $this->candidate->id ?? null,
            'test_id' => $this->test->id ?? null,
            'currentIndex' => $this->currentIndex
        ]);
    
        try {
            $session = session('test_session');
    
            if (!$session || $session['test_id'] != $this->test->id) {
                logger()->warning('[Livewire] Invalid session on timeout', ['session' => $session]);
                return redirect()->route('tests.start', ['id' => $this->test->id])
                    ->with('error', 'Session invalid or expired.');
            }
    
            logger()->info('[Livewire] Redirecting to submit route', [
                'expired' => true,
                'route' => route('tests.submit', ['id' => $this->test->id, 'expired' => 1])
            ]);
    
            return redirect()->route('tests.submit', ['id' => $this->test->id, 'expired' => 1]);
    
        } catch (\Throwable $e) {
            logger()->error('[Livewire] Exception in handleTimeExpiry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('tests.start', ['id' => $this->test->id])
                ->with('error', 'Failed to auto-submit due to error.');
        }
    }
    
    

    public function render()
    {
        $question = $this->questions[$this->currentIndex] ?? null;
        return view('livewire.test-player', compact('question'));
    }
}
