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
    public $isSubmitting = false;
    protected $currentQuestion = null;

    protected $rules = [
        'selectedAnswer' => 'nullable|exists:question_choices,id',
        'lsqValue' => 'nullable|integer|min:1|max:5',
    ];

    public function mount($test, $candidate, $questions, $currentIndex)
    {
        logger()->info('Livewire MOUNT called', [
            'incomingIndex' => $currentIndex,
            'sessionCurrentQuestion' => session('test_session.current_question') ?? 'none',
        ]);

        $this->test = $test;
        $this->candidate = $candidate;
        $this->questions = $questions;
        $this->currentIndex = $currentIndex;
        $this->currentQuestion = $this->getCurrentQuestion();

        $sessionData = session('test_session');
        if (!$sessionData) {
            logger()->error('No test session found during mount');
            return redirect()->route('tests.start', ['id' => $test->id]);
        }

        logger()->info('After setting currentIndex', [
            'this.currentIndex' => $this->currentIndex,
            'total_questions' => count($this->questions)
        ]);

        // Load saved answer if exists
        if (isset($sessionData['answers'][$this->currentIndex])) {
            $savedAnswer = $sessionData['answers'][$this->currentIndex];
            if ($this->currentQuestion->question_type === 'MCQ') {
                $this->selectedAnswer = is_numeric($savedAnswer) ? $savedAnswer : null;
            } else {
                $this->lsqValue = is_numeric($savedAnswer) ? $savedAnswer : 3;
            }
        }
    }

    public function getCurrentQuestion()
    {
        return $this->questions[$this->currentIndex] ?? null;
    }

    public function getQuestionProperty()
    {
        return $this->getCurrentQuestion();
    }

    public function getCurrentQuestionProperty()
    {
        return $this->getCurrentQuestion();
    }

    public function submitAndNext()
    {
        if ($this->isSubmitting) {
            logger()->warning('Preventing double submission');
            return;
        }

        $this->isSubmitting = true;
        
        try {
            $session = session('test_session');
            logger()->info('[Livewire] submitAndNext triggered', [
                'currentIndex' => $this->currentIndex,
                'total_questions' => count($this->questions)
            ]);
        
            if (!isset($this->questions[$this->currentIndex])) {
                logger()->error('Invalid currentIndex in Livewire', [
                    'index' => $this->currentIndex,
                    'total' => count($this->questions)
                ]);
                return;
            }
        
            $question = $this->getCurrentQuestion();
            $this->currentQuestion = $question;

            // Validate required selection
            if ($question->question_type === 'MCQ' && !$this->selectedAnswer) {
                $this->dispatch('showError', message: 'Please select an answer before proceeding.');
                $this->isSubmitting = false;
                return;
            }
        
            // Save MCQ Answer
            if ($question->question_type === 'MCQ' && $this->selectedAnswer) {
                $choice = $question->choices->firstWhere('id', $this->selectedAnswer);
                if ($choice) {
                    Answer::updateOrCreate(
                        [
                            'candidate_id' => $this->candidate->id,
                            'test_id' => $this->test->id,
                            'question_id' => $question->id,
                        ],
                        ['answer_text' => $choice->choice_text]
                    );
                    $session['answers'][$this->currentIndex] = $this->selectedAnswer;
                }
            }
        
            // Save LSQ Answer
            if ($question->question_type === 'LSQ') {
                Answer::updateOrCreate(
                    [
                        'candidate_id' => $this->candidate->id,
                        'test_id' => $this->test->id,
                        'question_id' => $question->id,
                    ],
                    ['answer_text' => $this->lsqValue]
                );
                $session['answers'][$this->currentIndex] = $this->lsqValue;
            }
        
            // Move index forward
            $this->currentIndex++;
            
            // Reset form values AFTER moving to next question
            $this->selectedAnswer = null;
            $this->lsqValue = 3;
            
            // Update current question
            $this->currentQuestion = $this->getCurrentQuestion();
        
            // Redirect to submit if we've reached the end
            if ($this->currentIndex >= count($this->questions)) {
                session(['test_session' => $session]);
                return redirect()->route('tests.submit', ['id' => $this->test->id]);
            }
            
            // Update session
            $session['current_question'] = $this->currentIndex;
            session(['test_session' => $session]);
            
            // Force a re-render
            $this->dispatch('questionChanged');
            
        } catch (\Exception $e) {
            logger()->error('Error in submitAndNext', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function updatedCurrentIndex()
    {
        $this->selectedAnswer = null;
        $this->lsqValue = 3;
        $this->dispatch('questionChanged');
    }

    public function hydrate()
    {
        // Reset values after re-hydration
        if ($this->currentQuestion && $this->currentQuestion->question_type === 'MCQ') {
            $this->selectedAnswer = null;
        }
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
        $this->currentQuestion = $this->getCurrentQuestion();
        logger()->info('Rendering question', [
            'index' => $this->currentIndex,
            'question_id' => $this->currentQuestion ? $this->currentQuestion->id : null
        ]);
        return view('livewire.test-player');
    }
}
