<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Question;
use App\Models\Invitation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;


class TestController extends Controller
{
    public function index()
    {
        $tests = Test::all();
        Log::info("web ", Auth::guard('web')->check()===TRUE? ["t"]:["f"]);
        return view('tests.index', compact('tests'));

    }

    public function create()
    {
        return view('tests.create');
    }
   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required',
            'invitation_link' => 'required|string|url',
        ]);
        
        
        $test = Test::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
            'admin_id' => auth()->id(),
        ]);

        
        $urlParts = explode('/', $validatedData['invitation_link']);
        $invitationToken = end($urlParts); 
        

        Invitation::create([
            'test_id' => $test->id,
            'invited_emails' => [],
            'expiration_date' => now()->addDays(7),
            'invitation_token' => $invitationToken,
            'invitation_link' => $validatedData['invitation_link'],
        ]);

        
        if ($request->file('file')->isValid()){
              try{
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();

                if (in_array($extension, ['xlsx', 'csv'])) {
                    $questions = $this->getQuestionsFromExcel($file);
                } elseif ($extension === 'json') {
                    $jsonContent = file_get_contents($file->getRealPath());
                    $questions = json_decode($jsonContent, true);
                }
                
                foreach ($questions as $q) {
                    $question = Question::create([
                        'test_id' => $test->id,
                        'question_text' => $q['Question'],
                        'question_type' => $q['Type'],
                    ]);

                    if($q['Type'] === 'MCQ'){
                        foreach ($choices = ['A', 'B', 'C', 'D'] as $choice) {
                            QuestionChoice::create([
                                'question_id' => $question->id,
                                'choice_text' => $q['Choice ' + $choice ?? ''],
                                'is_correct' => $q['Correct Answer'] === $choice
                            ]);
                        }
                    }

                    // Only create media if image URL exists
                    if (!empty($q['Image URL'])) {
                        QuestionMedia::create([
                            'question_id' => $question->id,
                            'file_path' => $q['Image URL'] ?? '',
                            'description' => $q['Image Description'] ?? ''
                        ]);
                    }

                }
            
                return redirect()->route('tests.show', $test->id)
                ->with('success', 'Test created and invitation link generated successfully!');

            }catch (\Exception $e) {
                Log::error('Error updating test:', [
                    'test_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()
                    ->with('error', 'Error updating test: ' . $e->getMessage());
            }

        }

        return redirect()->back()->with('error', 'Invalid file upload.');
    }

    public function edit($id)
    {
        $test = Test::findOrFail($id);
        return view('tests.edit', compact('test'));
    }

    public function update(Request $request, $id)
    {
        $test = Test::findOrFail($id);

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1', 
            'file' => 'nullable|file|mimes:xlsx,csv,json|max:2048'
        ]);
    
        
        $test->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
        ]);
        
        
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            try {
                $questionIds = $test->questions->pluck('id')->toArray();

                QuestionChoice::whereIn('question_id', $questionIds)->delete();
                QuestionMedia::whereIn('question_id', $questionIds)->delete();
                Question::whereIn('id', $questionIds)->delete();
     
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();

                if (in_array($extension, ['xlsx', 'csv'])) {
                    $questions = $this->getQuestionsFromExcel($file);
                } elseif ($extension === 'json') {
                    $jsonContent = file_get_contents($file->getRealPath());
                    $questions = json_decode($jsonContent, true);
                }
                
                foreach ($questions as $q) {
                    $question = Question::create([
                        'test_id' => $test->id,
                        'question_text' => $q['Question'] ?? '',
                        'question_type' => $q['Type'] ?? 'TEXT',
                    ]);
     
                    if (($q['Type'] ?? '') === 'MCQ') {
                        foreach ($choices = ['A', 'B', 'C', 'D'] as $choice) {
                            $choiceKey = 'Choice ' . $choice;
                            if (isset($q[$choiceKey]) && !empty($q[$choiceKey])) {
                                QuestionChoice::create([
                                    'question_id' => $question->id,
                                    'choice_text' => $q[$choiceKey],
                                    'is_correct' => ($q['Correct Answer'] ?? '') === $choice
                                ]);
                            }
                        }
                    }
     
                    if (!empty($q['Image URL'])) {
                        QuestionMedia::create([
                            'question_id' => $question->id,
                            'file_path' => $q['Image URL'],
                            'description' => $q['Image Description'] ?? ''
                        ]);
                    }
                }
     
                return redirect()->route('tests.show', $test->id)
                    ->with('success', 'Test and questions updated successfully!');
     
            } catch (\Exception $e) {
                Log::error('Error updating test:', [
                    'test_id' => $id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return redirect()->back()
                    ->with('error', 'Error updating test: ' . $e->getMessage());
            }
        }
    
        return redirect()->route('tests.show', $test->id)->with('success', 'Test updated successfully.');
    }
    
    public function destroy($id)
    {
        try{
            $test = Test::findOrFail($id);
            
            
            $questionIds = $test->questions->pluck('id')->toArray();

            QuestionChoice::whereIn('question_id', $questionIds)->delete();
            QuestionMedia::whereIn('question_id', $questionIds)->delete();
            Question::whereIn('id', $questionIds)->delete();
            
            $test->delete();

            return redirect()->route('tests.index')
                ->with('success', 'Test and all associated data deleted successfully!');
        }catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting test:', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()->back()
                ->with('error', 'Error deleting test: ' . $e->getMessage());
        }
    }

    public function invite($id){
        return view('tests.invite', compact('id'));
    }

    protected function getQuestionsFromExcel($file)
    {
        $questions = [];
        if ($file) {
            $questions = Excel::toArray(new QuestionsImport, $file);
            $questions = $questions[0] ?? [];
        }
        return $questions;
    }

    public function show($id)
    {
        if (Auth::guard('web')->check()) {
            $test = Test::with(['questions.choices', 'questions.media', 'admin'])->findOrFail($id);
            $questions = $test->questions()
            ->with(['choices', 'media'])
            ->orderBy('id', 'asc')
            ->get();

            return view('tests.show', compact('test', 'questions'));

        } elseif (Auth::guard('candidate')->check()) {
            try {
                $invitation = Invitation::where('test_id', $id)
                    ->where('invitation_token', request()->token)
                    ->where('expiration_date', '>', now())
                    ->firstOrFail();
                
                $test = Test::with(['questions.choices', 'questions.media'])
                    ->where('id', $invitation->test_id)
                    ->firstOrFail();

                $questions = $test->questions()
                ->with(['choices', 'media'])
                ->orderBy('id', 'asc')
                ->get();
    
                $candidate = Auth::guard('candidate')->user();
                
                $testAttempt = $candidate->tests()
                    ->wherePivot('test_id', $id)
                    ->first();
    
                $isTestStarted = $testAttempt && $testAttempt->pivot->started_at;
                $isTestCompleted = $testAttempt && $testAttempt->pivot->completed_at;
                $isInvitationExpired = $invitation->expiration_date < now();
                
                $remainingTime = null;
                $testSession = session('test_session');
                
                if ($isTestStarted && $testSession) {
                    $endTime = Carbon::parse($testSession['end_time']);
                    $remainingTime = max(0, now()->diffInSeconds($endTime, false));
                }
                
                return view('tests.show', compact('test', 'isTestStarted', 'question',
                    'isTestCompleted', 'isInvitationExpired', 'remainingTime'));

            } catch (\Exception $e) {
                Log::error('Invalid test access attempt:', [
                    'test_id' => $id,
                    'token' => request()->token,
                    'error' => $e->getMessage()
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Invalid or expired test invitation.');
            }
        }
        return redirect()->route('login')
        ->with('error', 'Unauthorized access');
    }

    public function startTest(Request $request, $id)
    {
        if (!Auth::guard('candidate')->check()) {
            return redirect()->route('invitation.candidate-auth')
                ->with('error', 'Unauthorized access to the test.');
        }

        $candidate = Auth::guard('candidate')->user();
        $test = Test::with(['questions.choices', 'questions.media'])->findOrFail($id);
        $questions = $test->questions;
        
        // Check if completed
        $isCompleted = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->whereNotNull('candidate_test.completed_at')
            ->exists();
        
        if ($isCompleted) {
            return redirect()->route('tests.result', ['id' => $id])
                ->with('info', 'You have already completed this test.');
        }
        
        $testSession = session('test_session', []);
        
        if (!isset($testSession['test_id']) || $testSession['test_id'] != $id) {
            $startTime = now();
            $endTime = $startTime->copy()->addMinutes($test->duration);
        
            $testSession = [
                'test_id' => $test->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'current_question' => 0,
                'answers' => []
            ];
        
            $candidate->tests()->attach($id, [
                'started_at' => $startTime
            ]);
        
        } else {
            $startTime = Carbon::parse($testSession['start_time']);
            $endTime = $startTime->copy()->addMinutes($test->duration);
        
            if (!isset($testSession['end_time']) || !Carbon::hasFormat($testSession['end_time'], 'Y-m-d H:i:s')) {
                $testSession['end_time'] = $endTime->toDateTimeString();
            } else {
                $endTime = Carbon::parse($testSession['end_time']);
            }
        }

        
        if (now()->gt($endTime)) {
            return $this->handleExpiredTest($test);
        }

        session(['test_session' => $testSession]);

        $currentQuestionIndex = $testSession['current_question'];

        return view('tests.start', compact('test', 'questions', 'currentQuestionIndex'));
    }

    public function nextQuestion(Request $request, $id)
    {   
        if ($this->checkTimeUp()) {
            return $this->submitTest($request, $id);
        }

        $test = Test::with(['questions.choices', 'questions.media'])->findOrFail($id);
        $questions = $test->questions;

        
        $request->validate([
            'current_index' => 'required|numeric',
            'answer' => 'nullable|in:a,b,c,d'
        ]);

        $testSession = session('test_session');
        if (!$testSession || $testSession['test_id'] != $id) {
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid test session.');
        }

        $currentIndex = $request->input('current_index');
        $answer = $request->input('answer', ''); 
        $testSession['answers'][$currentIndex] = $answer;

        $nextIndex = $currentIndex + 1;
        
        if ($nextIndex >= count($questions)) {
            return $this->submitTest($request, $id);
        }

        session()->put('test_session', $testSession);
        session()->put('test_session.current_question', $nextIndex);

        return redirect()->route('tests.start', ['id' => $id]);
    }
    
    public function submitTest(Request $request, $id)
    {
        Log::info('Starting test submission process', [
            'test_id' => $id,
            'is_expired' => $request->boolean('expired'),
            'timestamp' => now()
        ]);
    
        try {
            $candidate = Auth::guard('candidate')->user();
            $test = Test::with(['questions.choices'])->findOrFail($id);
            $questions = $test->questions;
            $testSession = session('test_session');
    
            if (!$testSession || $testSession['test_id'] != $id) {
                Log::error('Invalid test session', [
                    'session' => $testSession,
                    'requested_test_id' => $id
                ]);
                throw new \Exception('Invalid test session');
            }
    
            
            $answers = $testSession['answers'] ?? [];
            
            // capturing the final answer 
            $currentIndex = $request->input('current_index');
            $finalAnswer = $request->input('answer', ''); 
            $answers[$currentIndex] = $finalAnswer; 
            
            // unanswered Qs
            foreach ($questions as $index => $question) {
                $answerText = $answers[$index] ?? '';

                Answer::create([
                    'candidate_id' => $candidate->id,
                    'question_id' => $question->id,
                    'answer_text'=> $answerText 
                ]);

                // count scores
                if ($question->question_type === 'MCQ'){
                    $correctChioce = $question->choices()->where('is_correct', true)->first();

                    if ($correctChioce && strtolower($answerText) === strtolower($correctChioce->choice_text)){
                        $score++;
                    }
                }
            }
    
            
            $now = now(); 
            
            $candidate->tests()->updateExistingPivot($id, [
                'completed_at' => $now,
                'score' => $score,
            ]);
    
            Log::info('Test data saved successfully', [
                'test_id' => $id,
                'score' => $score,
            ]);
    
            
            session()->forget('test_session');
    
            $message = $request->boolean('expired')
                ? 'Test time has expired. Your answers have been submitted automatically.'
                : 'Test completed successfully!';
            
            return redirect()->route('tests.result', ['id' => $id])
                ->with($request->boolean('expired') ? 'warning' : 'success', $message);
    
        } catch (\Exception $e) {
            Log::error('Exception in submitTest', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Failed to submit test',
                    'message' => $e->getMessage()
                ], 500);
            }
            
            throw $e;
        }
    }


    public function handleExpiredTest($test)
    {
        Log::info('Handling expired test', ['test_id' => $test->id]);
        
        $testSession = session('test_session');
        $answers = $testSession['answers'] ?? [];
        $test = Test::with(['questions.choices'])->findOrFail($id);
        $questions = $test->questions;
        $candidate = Auth::guard('candidate')->user();

        
        $score = 0;
        // unanswered Qs
        foreach ($questions as $index => $question) {
            $answerText = $answers[$index] ?? '';

            Answer::create([
                'candidate_id' => $candidate->id,
                'question_id' => $question->id,
                'answer_text'=> $answerText 
            ]);

            // count scores
            if ($question->question_type === 'MCQ'){
                $correctChioce = $question->choices()->where('is_correct', true)->first();

                if ($correctChioce && strtolower($answerText) === strtolower($correctChioce->choice_text)){
                    $score++;
                }
            }
        }
        
        $now = now();
  
        $candidate->tests()->updateExistingPivot($test->id, [
            'completed_at' => $now,
            'score' => $score,
        ]);

        session()->forget('test_session');

        return redirect()->route('tests.result', ['id' => $test->id])
            ->with('warning', 'Test time has expired. Your answers have been submitted automatically.');
    }

    public function showResult($id)
    {
        $candidate = Auth::guard('candidate')->user();
        $test = Test::with([
            'questions.choices',
            'questions.media',
            'questions.answers' => function($query) use ($candidate) {
                $query->where('candidate_id', $candidate->id);
            }
        ])->findOrFail($id);
        $questions = $test->questions;
        $now = now();
        
        $testAttempt = $candidate->tests()
            ->where('test_id', $id)
            ->first();
            
        if (!$testAttempt) {
            return redirect()->route('candidate.dashboard')
                ->with('error', 'No test attempt found.');
        }

   

        $startTime = $testAttempt->pivot->started_at;
        $endTime = Carbon::parse($startTime)->addMinutes($test->duration);
        $isExpired = $endTime->isPast();

        return view('tests.result', [
            'test' => $test,
            'candidate' => $candidate,
            'testStatus' => $testAttempt,
            'questions' => $questions,
            'isExpired' => $tisExpired
        ]);
    }

    private function checkTimeUp($testAttempt)
    {
        if (!$testAttempt->pivot->started_at) {
            return false;
        }

        $startTime = Carbon::parse($testAttempt->pivot->started_at);
        $test = Test::find($testAttempt->pivot->test_id);
        $endTime = $startTime->copy()->addMinutes($test->duration);

        if (now()->gt($endTime)) {
            Log::info('Test time is up', [
                'test_id' => $test->id,
                'end_time' => $endTime
            ]);
            return true;
        }

        return false;
    }
}