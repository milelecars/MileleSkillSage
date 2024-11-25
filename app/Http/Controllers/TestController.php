<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TestInvitation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


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

    public function update(Request $request, $id)
    {
        $test = Test::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1', 
            'file' => 'nullable|file|mimes:xlsx,csv,json|max:2048'
        ]);
    
        
        $test->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
        ]);
        
        
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            
            if ($test->questions_file_path) {
                Storage::disk('public')->delete($test->questions_file_path);
            }
    
            $filePath = $request->file('file')->store('questions', 'public');
            
            
            $test->update(['questions_file_path' => $filePath]);
    
            
            $extension = $request->file('file')->getClientOriginalExtension();
            
            if (in_array($extension, ['xlsx', 'csv'])) {
                Excel::import(new QuestionsImport($test), $request->file('file'));
            } elseif ($extension === 'json') {
                $jsonContent = file_get_contents($request->file('file')->getRealPath());
                $questions = json_decode($jsonContent, true);
                
                
            }
    
            return redirect()->route('tests.index')->with('success', 'Test updated and questions processed successfully.');
        }
    
        return redirect()->route('tests.show', $test->id)->with('success', 'Test updated successfully.');
    }
   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required',
            'invitation_link' => 'required|string|url',
            'file' => 'required|file|mimes:xlsx,csv,json',
        ]);

        
        $urlParts = explode('/', $validatedData['invitation_link']);
        $invitationToken = end($urlParts); 

        
        $test = Test::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
        ]);

        
        TestInvitation::create([
            'test_id' => $test->id,
            'invitation_link' => $validatedData['invitation_link'],
            'invitation_token' => $invitationToken,
            'email_list' => [],
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
        ]);

        
        if ($request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('questions', 'public');
            
            
            $test->update(['questions_file_path' => $filePath]);

            
            $extension = $request->file('file')->getClientOriginalExtension();
            
            if (in_array($extension, ['xlsx', 'csv'])) {
                Excel::import(new QuestionsImport($test), $request->file('file'));
            } elseif ($extension === 'json') {
                $jsonContent = file_get_contents($request->file('file')->getRealPath());
                $questions = json_decode($jsonContent, true);
                
                
            }

            
            return redirect()->route('tests.show', $test->id)
            ->with('success', 'Test created and invitation link generated successfully!');
        }

        return redirect()->back()->with('error', 'Invalid file upload.');
    }

    public function edit($id)
    {
        $test = Test::findOrFail($id);
        return view('tests.edit', compact('test'));
    }

    public function destroy($id)
    {
        $test = Test::findOrFail($id);
        
        
        if ($test->questions_file_path) {
            Storage::disk('public')->delete($test->questions_file_path);
        }
        
        $test->delete();

        return redirect()->route('tests.index')
            ->with('success', 'Test and all associated data deleted successfully!');
    }

    public function invite($id){
        return view('tests.invite', compact('id'));
    }

    protected function getQuestionsFromExcel($test)
    {
        $questions = [];
        if ($test->questions_file_path) {
            $filePath = storage_path('app/public/' . $test->questions_file_path);
            $questions = Excel::toArray(new QuestionsImport($test), $filePath);
            $questions = $questions[0] ?? [];
        }
        return $questions;
    }

    public function show($id)
    {
        if (Auth::guard('web')->check()) {
            Log::info("web ");
            $test = Test::with('invitation')->findOrFail($id);
            $questions = [];
            if ($test->questions_file_path) {
                $filePath = storage_path('app/public/' . $test->questions_file_path);
                $questions = Excel::toArray(new QuestionsImport($test), $filePath);
                $questions = $questions[0] ?? [];
            }
            return view('tests.show', compact('test', 'questions'));
        } elseif (Auth::guard('candidate')->check()) {
            Log::info("Candidate ");
            $test = Test::with('invitation')->findOrFail($id);
            $questions = $this->getQuestionsFromExcel($test);
            $candidate = Auth::guard('candidate')->user();
            
            $isTestStarted = $candidate->tests()
                ->wherePivot('test_id', $id)
                ->wherePivotNotNull('started_at')
                ->exists();
    
            $isTestCompleted = $candidate->tests()
                ->wherePivot('test_id', $id)
                ->wherePivotNotNull('completed_at')
                ->exists();
            
            $isInvitationExpired = $test->invitation && $test->invitation->expires_at < now();
            
            $remainingTime = null;
            $testSession = session('test_session');
            
            if ($isTestStarted && $testSession) {
                $endTime = Carbon::parse($testSession['end_time']);
                $remainingTime = max(0, now()->diffInSeconds($endTime, false));
            }
            
            return view('tests.show', compact('test', 'questions', 'isTestStarted', 
                'isTestCompleted', 'isInvitationExpired', 'remainingTime'));
        }
    }

    public function startTest(Request $request, $id)
    {
        if (!Auth::guard('candidate')->check()) {
            return redirect()->route('invitation.candidate-auth')
                ->with('error', 'Unauthorized access to the test.');
        }

        $candidate = Auth::guard('candidate')->user();
        $test = Test::findOrFail($id);

        $isCompleted = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->whereNotNull('test_candidate.completed_at')
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

            $candidate->test_started_at = $startTime;
            $candidate->save();

            $candidate->tests()->syncWithoutDetaching([
                $id => ['started_at' => $startTime]
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

        $questions = $this->getQuestionsFromExcel($test);
        $currentQuestionIndex = $testSession['current_question'];

        return view('tests.start', compact('test', 'questions', 'currentQuestionIndex'));
    }

    public function nextQuestion(Request $request, $id)
    {
        Log::info('Processing next question', ['test_id' => $id]);
        
        if ($this->checkTimeUp()) {
            Log::info('Time is up during next question', ['test_id' => $id]);
            return $this->submitTest($request, $id);
        }

        $test = Test::findOrFail($id);
        $questions = $this->getQuestionsFromExcel($test);

        
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
            $test = Test::with('questions.choices')->findOrFail($id);
            $questions = $test->questions;
            Log::info('Loaded test and questions for submission', [
                'test_id' => $id,
                'candidate_id' => $candidate->id,
                'question_count' => $questions->count()
            ]);

            $testAttempt = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->first();

            if (!$testAttempt) {
                Log::error('Test attempt not found', ['test_id' => $id, 'candidate_id' => $candidate->id]);
                return redirect()->route('tests.start', ['id' => $id])
                    ->with('error', 'Test attempt not found.');
            }

            $request->validate([
                'current_index' => 'required|numeric',
                'answer' => 'nullable|exists:question_choices,id', 
            ]);
    
            Log::info('in submit');
    
            $testSession = session('test_session');
    
            if (!$testSession || $testSession['test_id'] != $id) {
                Log::error('Invalid test session', [
                    'session' => $testSession,
                    'requested_test_id' => $id
                ]);
                throw new \Exception('Invalid test session');
            }
    
            
            $answers = $testSession['answers'] ?? [];
            
            
            $currentIndex = $request->input('current_index');
            $finalAnswer = $request->input('answer', ''); 
            $answers[$currentIndex] = $finalAnswer; 
            
            $questions = $this->getQuestionsFromExcel($test);
            
            
            for ($i = 0; $i < count($questions); $i++) {
                if (!isset($answers[$i])) {
                    $answers[$i] = "";
                }
            }
    
            
            $now = now(); 
            $score = $this->calculateScore($questions, $answers);
    
            
            $candidate->tests()->updateExistingPivot($id, [
                'completed_at' => $now,
                'answers' => $answers,
                'score' => $score,
                'ip_address' => $realIP,
            ]);
            Log::info('Test completed successfully', [
                'test_id' => $id,
                'candidate_id' => $candidate->id,
                'score' => $score,
                'ip_address' => $realIP,
            ]);
    
            
            $candidate->update([
                'test_completed_at' => $now,
                'test_score' => $score,
                'test_name' => $test->name,
                'test_answers'=> $answers
            ]);
    
            Log::info('Test data saved successfully', [
                'test_id' => $id,
                'score' => $score,
                'answers' => $answers
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
        $questions = $this->getQuestionsFromExcel($test);
        $candidate = Auth::guard('candidate')->user();

        
        $score = $this->calculateScore($questions, $answers);
        $now = now();

        
        for ($i = 0; $i < count($questions); $i++) {
            if (!isset($answers[$i])) {
                $answers[$i] = "";
                Log::debug("Empty answer filled for question $i in expired test");
            }
        }

        
        $candidate->tests()->updateExistingPivot($test->id, [
            'completed_at' => $now,
            'answers' => $answers,
            'score' => $score,
            'ip_address' => $realIP,
        ]);
        
        
        $candidate->update([
            'test_completed_at' => $now,
            'test_score' => $score,
            'test_name' => $test->name,
            'test_answers'=> $answers
        ]);


        session()->forget('test_session');

        return redirect()->route('tests.result', ['id' => $test->id])
            ->with('warning', 'Test time has expired. Your answers have been submitted automatically.');
    }

    private function calculateScore($questions, $answers)
    {
        $score = 0;
        foreach ($answers as $index => $answer) {
            if (isset($questions[$index]) &&
                strtolower($answer) === strtolower($questions[$index]['answer'])) {
                $score++;
            }
        }
        return $score;
    }

    public function showResult($id)
    {
        $candidate = Auth::guard('candidate')->user();
        $test = Test::findOrFail($id);
        
        $testAttempt = $candidate->tests()
            ->where('test_id', $id)
            ->first();
            
        if (!$testAttempt) {
            return redirect()->route('candidate.dashboard')
                ->with('error', 'No test attempt found.');
        }

        $questions = $this->getQuestionsFromExcel($test);
        
        return view('tests.result', [
            'test' => $test,
            'candidate' => $candidate,
            'testStatus' => $testAttempt,
            'questions' => $questions,
            'isExpired' => $testAttempt->pivot->is_expired ?? false
        ]);
    }

    private function checkTimeUp()
    {
        $testSession = session('test_session');
        if ($testSession) {
            $endTime = Carbon::parse($testSession['end_time']);
            if (now()->gt($endTime)) {
                Log::info('Test time is up', [
                    'test_id' => $testSession['test_id'],
                    'end_time' => $endTime
                ]);
                return true;
            }
        }
        return false;
    }
}