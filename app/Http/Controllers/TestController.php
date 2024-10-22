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
            'duration' => 'required|integer|min:1', // Added validation for duration
            'file' => 'nullable|file|mimes:xlsx,csv,json|max:2048'
        ]);
    
        // Update the test attributes
        $test->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
        ]);
        
        // Handle the file upload
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            // Delete the old file if it exists
            if ($test->questions_file_path) {
                Storage::disk('public')->delete($test->questions_file_path);
            }
    
            $filePath = $request->file('file')->store('questions', 'public');
            
            // Save the new file path to the database
            $test->update(['questions_file_path' => $filePath]);
    
            // Process the file based on its type
            $extension = $request->file('file')->getClientOriginalExtension();
            
            if (in_array($extension, ['xlsx', 'csv'])) {
                Excel::import(new QuestionsImport($test), $request->file('file'));
            } elseif ($extension === 'json') {
                $jsonContent = file_get_contents($request->file('file')->getRealPath());
                $questions = json_decode($jsonContent, true);
                // Here you might want to process and save the JSON data to your database
                // This depends on how you want to store and use the data
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

        // Extract the token from the invitation link
        $urlParts = explode('/', $validatedData['invitation_link']);
        $invitationToken = end($urlParts); // Get the last part of the URL

        // Store the test first
        $test = Test::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'duration' => $validatedData['duration'],
        ]);

        // Create test invitation with the token and link it to the test
        TestInvitation::create([
            'test_id' => $test->id,
            'invitation_link' => $validatedData['invitation_link'],
            'invitation_token' => $invitationToken,
            'email_list' => [],
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
        ]);

        // Handle the file upload
        if ($request->file('file')->isValid()) {
            $filePath = $request->file('file')->store('questions', 'public');
            
            // Save the file path to the database
            $test->update(['questions_file_path' => $filePath]);

            // Process the file based on its type
            $extension = $request->file('file')->getClientOriginalExtension();
            
            if (in_array($extension, ['xlsx', 'csv'])) {
                Excel::import(new QuestionsImport($test), $request->file('file'));
            } elseif ($extension === 'json') {
                $jsonContent = file_get_contents($request->file('file')->getRealPath());
                $questions = json_decode($jsonContent, true);
                // Here you might want to process and save the JSON data to your database
                // This depends on how you want to store and use the data
            }

            // return redirect()->route('tests.index')->with('success', 'Test created and questions processed successfully.');
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
        
        // Delete the file if it exists
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
            $filePath = storage_path( 'app/public/' . $test->questions_file_path);
            $questions = Excel:: toArray (new QuestionsImport ($test), $filePath);
            $questions = $questions[0] ?? []; // Assuming we're only interested in the first sheet
            }
            return view('tests.show', compact( 'test', 'questions'));

        }elseif (Auth::guard('candidate')->check()) {
            Log::info("Candidate ");
            $test = Test::with('invitation')->findOrFail($id);
            $questions = $this->getQuestionsFromExcel($test);
            $candidate = Auth::guard('candidate')->user();
            
            $isTestStarted = $candidate->tests()
                ->wherePivot('test_id', $id)
                ->wherePivotNotNull('started_at')
                ->exists();
    
            $isTestCompleted = $candidate->test_completed_at !== null || $candidate->tests()->wherePivot('test_id', $id)->wherePivot('completed_at', '!=', null)->exists();
            
            $isInvitationExpired = $test->invitation && $test->invitation->expires_at < now();
            
            $remainingTime = null;
            
            if ($isTestStarted) {
                $startTime = Carbon::parse($testSession['start_time']);
                $endTime = $startTime->copy()->addMinutes($test->duration);
                $remainingTime = max(0, now()->diffInSeconds($endTime, false));
            }
            
            return view('tests.show', compact('test', 'questions', 'isTestStarted', 'isTestCompleted', 'isInvitationExpired', 'remainingTime'));

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
            // New test session
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
            // Existing test session
            $startTime = Carbon::parse($testSession['start_time']);
            $endTime = $startTime->copy()->addMinutes($test->duration);

            // Update end_time if it's not set or invalid
            if (!isset($testSession['end_time']) || !Carbon::hasFormat($testSession['end_time'], 'Y-m-d H:i:s')) {
                $testSession['end_time'] = $endTime->toDateTimeString();
            } else {
                $endTime = Carbon::parse($testSession['end_time']);
            }
        }

        // Check if test has expired
        if (now()->gt($endTime)) {
            return $this->handleExpiredTest($test);
        }

        // Update session
        session(['test_session' => $testSession]);

        $questions = $this->getQuestionsFromExcel($test);
        $currentQuestionIndex = $testSession['current_question'];

        return view('tests.start', compact('test', 'questions', 'currentQuestionIndex'));
    }

    public function nextQuestion(Request $request, $id)
    {
        if ($this->checkTimeUp()) {
            return redirect()->route('tests.result', ['id' => $id]);
        }
        
        $test = Test::findOrFail($id);
        $questions = $this->getQuestionsFromExcel($test);
        
        $request->validate([
            'answer' => 'required|in:a,b,c,d',
            'current_index' => 'required|numeric',
        ]);

        $testSession = session('test_session');
        if (!$testSession || $testSession['test_id'] != $id) {
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid test session.');
        }

        $endTime = Carbon::parse($testSession['end_time']);
        if (now()->gt($endTime)) {
            return $this->handleExpiredTest($test);
        }

        $currentIndex = $request->input('current_index');
        $answer = $request->input('answer');
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
        $candidate = Auth::guard('candidate')->user();
        $test = Test::findOrFail($id);
        $testSession = session('test_session');

        if (!$testSession || $testSession['test_id'] != $id) {
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid test session.');
        }

        $endTime = Carbon::parse($testSession['end_time']);
        if (now()->gt($endTime)) {
            return $this->handleExpiredTest($test);
        }

        // Capture the final answer
        $currentIndex = $request->input('current_index');
        $finalAnswer = $request->input('answer');
        if ($finalAnswer) {
            $testSession['answers'][$currentIndex] = $finalAnswer;
        }

        $answers = $testSession['answers'];
        $questions = $this->getQuestionsFromExcel($test);
        $score = $this->calculateScore($questions, $answers);

        $candidate->update([
            'test_completed_at' => now(),
            'test_score' => $score,
            'test_name' => $test->name
        ]);

        $candidate->tests()->updateExistingPivot($id, [
            'completed_at' => now(),
            'score' => $score
        ]);

        session()->forget('test_session');

        return redirect()->route('tests.result', ['id' => $id])
            ->with('success', 'Test completed successfully!');
    }

    private function handleExpiredTest($test)
    {
        $testSession = session('test_session');
        $answers = $testSession['answers'] ?? [];
        $questions = $this->getQuestionsFromExcel($test);
        $candidate = Auth::guard('candidate')->user();

        $score = $this->calculateScore($questions, $answers);

        $candidate->update([
            'test_completed_at' => now(),
            'test_score' => $score,
            'test_name' => $test->name
        ]);

        $candidate->tests()->updateExistingPivot($test->id, [
            'completed_at' => now(),
            'score' => $score
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
        $test = Test::findOrFail($id);
        $candidate = Auth::guard('candidate')->user();
        $questions = $this->getQuestionsFromExcel($test);
        
        $testStatus = $candidate->tests()->where('test_id', $id)->first();

        // Set session data
        session([
            'current_test_id' => $id,
            'test' => $test
        ]);

        return view('tests.result', compact('test', 'questions', 'candidate', 'testStatus'));
    }

    private function checkTimeUp()
    {
        $testSession = session('test_session');
        if ($testSession) {
            $endTime = Carbon::parse($testSession['end_time']);
            if (now()->gt($endTime)) {
                session()->forget('test_session');
                return true;
            }
        }
        return false;
    }
}