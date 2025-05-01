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

        $question = $this->questions[$this->currentIndex];

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

        $this->currentIndex++;
        if ($this->currentIndex >= count($this->questions)) {
            return redirect()->route('tests.submit', ['id' => $this->test->id, 'current_index' => $this->currentIndex]);
        }

        $session['current_question'] = $this->currentIndex;
        session(['test_session' => $session]);

        $this->reset(['selectedAnswer', 'lsqValue']);
    }

    public function render()
    {
        $question = $this->questions[$this->currentIndex] ?? null;
        return view('livewire.test-player', compact('question'));
    }
}
