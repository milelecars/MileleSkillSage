<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuestionMedia;
use App\Models\Invitation;
use App\Models\Answer;
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
        Log::info('Starting test creation process', [
            'admin_id' => auth()->id(),
            'request_data' => $request->except(['file'])  // Exclude file to keep logs clean
        ]);
    
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'duration' => 'required',
                'invitation_link' => 'required|string|url',
            ]);
    
            Log::info('Validation passed', [
                'validated_data' => $validatedData
            ]);
    
            // Create test
            $test = Test::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'duration' => $validatedData['duration'],
                'admin_id' => auth()->id(),
            ]);
    
            Log::info('Test created successfully', [
                'test_id' => $test->id,
                'test_data' => $test->toArray()
            ]);
    
            // Parse invitation link
            $urlParts = explode('/', $validatedData['invitation_link']);
            $invitationToken = end($urlParts);
    
            Log::info('Parsed invitation link', [
                'url_parts' => $urlParts,
                'token' => $invitationToken
            ]);
    
            // Create invitation
            $invitation = Invitation::create([
                'test_id' => $test->id,
                'invited_emails' => json_encode([]),
                'expiration_date' => now()->addDays(7),
                'invitation_token' => $invitationToken,
                'invitation_link' => $validatedData['invitation_link'],
            ]);
    
            Log::info('Invitation created', [
                'invitation_id' => $invitation->id,
                'invitation_data' => $invitation->toArray()
            ]);
    
            if (!$request->hasFile('file')) {
                Log::warning('No file uploaded', [
                    'test_id' => $test->id
                ]);
                return redirect()->back()->with('error', 'No file uploaded.');
            }
    
            $file = $request->file('file');
            if (!$file->isValid()) {
                Log::error('Invalid file upload', [
                    'test_id' => $test->id,
                    'original_name' => $file->getClientOriginalName(),
                    'error' => $file->getError()
                ]);
                return redirect()->back()->with('error', 'Invalid file upload.');
            }
    
            $extension = $file->getClientOriginalExtension();
            Log::info('Processing uploaded file', [
                'test_id' => $test->id,
                'file_name' => $file->getClientOriginalName(),
                'extension' => $extension,
                'size' => $file->getSize()
            ]);
    
            // Parse questions based on file type
            if (in_array($extension, ['xlsx', 'csv'])) {
                $questions = $this->getQuestionsFromExcel($file, $test);
                Log::info('Parsed Excel/CSV file', [
                    'question_count' => count($questions)
                ]);
            } elseif ($extension === 'json') {
                $jsonContent = file_get_contents($file->getRealPath());
                $questions = json_decode($jsonContent, true);
                Log::info('Parsed JSON file', [
                    'question_count' => count($questions)
                ]);
            } else {
                Log::error('Unsupported file type', [
                    'extension' => $extension
                ]);
                return redirect()->back()->with('error', 'Unsupported file type.');
            }

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();

                Log::info('Processing file', [
                    'extension' => $extension,
                    'filename' => $file->getClientOriginalName()
                ]);
    
                try {
                    if (in_array($extension, ['xlsx', 'csv'])) {
                        $import = new QuestionsImport($test);
                        $collection = Excel::toCollection($import, $file);
                        
                        $questions = $collection->first()->toArray();
                        
                        Log::info('Questions imported from Excel', [
                            'question_count' => count($questions),
                            'sample' => !empty($questions) ? $questions[0] : null
                        ]);
                    } elseif ($extension === 'json') {
                        $jsonContent = file_get_contents($file->getRealPath());
                        $questions = json_decode($jsonContent, true);
                        
                        Log::info('Questions parsed from JSON', [
                            'question_count' => count($questions)
                        ]);
                    }
                    
                    if (empty($questions)) {
                        throw new \Exception('No questions found in the file.');
                    }
    
                    // Create questions and related data
                    foreach ($questions as $q) {
                        Log::info('Processing question', ['question' => $q]);
                        
                        $question = Question::create([
                            'test_id' => $test->id,
                            'question_text' => $q['question'],
                            'question_type' => $q['type'],
                        ]);

                        if ($q['type'] === 'MCQ') {
                            // Create choices
                            $choices = [
                                'a' => $q['choice_a'],
                                'b' => $q['choice_b'],
                                'c' => $q['choice_c'],
                                'd' => $q['choice_d']
                            ];

                            foreach ($choices as $key => $text) {
                                QuestionChoice::create([
                                    'question_id' => $question->id,
                                    'choice_text' => $text,
                                    'is_correct' => strtoupper($key) === $q['answer']
                                ]);
                            }
                        }

                        if (!empty($q['image_url'])) {
                            QuestionMedia::create([
                                'question_id' => $question->id,
                                'image_url' => $q['image_url'],
                                'description' => $q['image_description'] ?? ''
                            ]);
                        }
                    }

                    Log::info('Questions created successfully', [
                        'test_id' => $test->id,
                        'total_questions' => count($questions)
                    ]);

                    return redirect()->route('tests.show', $test->id)
                        ->with('success', 'Test created successfully with ' . count($questions) . ' questions!');
    
                
                } catch (\Exception $e) {
                    Log::error('Error processing questions file', [
                        'test_id' => $test->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in test creation process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth()->id()
            ]);
    
            return redirect()->back()
                ->with('error', 'Error creating test: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $test = Test::findOrFail($id);
        return view('tests.edit', compact('test'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Starting test update process', [
            'test_id' => $id,
            'admin_id' => auth()->id(),
            'request_data' => $request->except(['file'])
        ]);

        try {
            $test = Test::findOrFail($id);
            
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'required|integer|min:1',
                'file' => 'nullable|file|mimes:xlsx,csv,json|max:2048'
            ]);

            Log::info('Validation passed for test update', [
                'validated_data' => $validatedData
            ]);

            // Update test details
            $test->update([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'duration' => $validatedData['duration'],
            ]);

            Log::info('Test details updated successfully', [
                'test_id' => $test->id,
                'updated_data' => $test->toArray()
            ]);

            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();

                Log::info('Processing file for test update', [
                    'test_id' => $test->id,
                    'file_name' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'size' => $file->getSize()
                ]);

                try {
                    // Delete existing questions and related data
                    $questionIds = $test->questions->pluck('id')->toArray();
                    
                    Log::info('Deleting existing questions', [
                        'test_id' => $test->id,
                        'question_ids' => $questionIds
                    ]);

                    QuestionChoice::whereIn('question_id', $questionIds)->delete();
                    QuestionMedia::whereIn('question_id', $questionIds)->delete();
                    Question::whereIn('id', $questionIds)->delete();

                    // Import new questions
                    if (in_array($extension, ['xlsx', 'csv'])) {
                        $import = new QuestionsImport($test);
                        $collection = Excel::toCollection($import, $file);
                        $questions = $collection->first()->toArray();
                        
                        Log::info('Questions imported from Excel for update', [
                            'question_count' => count($questions),
                            'sample' => !empty($questions) ? $questions[0] : null
                        ]);
                    } elseif ($extension === 'json') {
                        $jsonContent = file_get_contents($file->getRealPath());
                        $questions = json_decode($jsonContent, true);
                        
                        Log::info('Questions parsed from JSON for update', [
                            'question_count' => count($questions)
                        ]);
                    }

                    if (empty($questions)) {
                        throw new \Exception('No questions found in the uploaded file.');
                    }

                    // Create new questions
                    foreach ($questions as $q) {
                        Log::info('Processing question for update', ['question' => $q]);
                        
                        $question = Question::create([
                            'test_id' => $test->id,
                            'question_text' => $q['question'],
                            'question_type' => $q['type'],
                        ]);

                        if ($q['type'] === 'MCQ') {
                            $choices = [
                                'a' => $q['choice_a'],
                                'b' => $q['choice_b'],
                                'c' => $q['choice_c'],
                                'd' => $q['choice_d']
                            ];

                            foreach ($choices as $key => $text) {
                                QuestionChoice::create([
                                    'question_id' => $question->id,
                                    'choice_text' => $text,
                                    'is_correct' => strtoupper($key) === $q['answer']
                                ]);
                            }
                        }

                        if (!empty($q['image_url'])) {
                            QuestionMedia::create([
                                'question_id' => $question->id,
                                'image_url' => $q['image_url'],
                                'description' => $q['image_description'] ?? ''
                            ]);
                        }
                    }

                    Log::info('Questions updated successfully', [
                        'test_id' => $test->id,
                        'total_questions' => count($questions)
                    ]);

                    return redirect()->route('tests.show', $test->id)
                        ->with('success', 'Test updated successfully with ' . count($questions) . ' questions!');

                } catch (\Exception $e) {
                    Log::error('Error processing questions during update', [
                        'test_id' => $test->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    throw $e;
                }
            }

            return redirect()->route('tests.show', $test->id)
                ->with('success', 'Test details updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error in test update process', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('error', 'Error updating test: ' . $e->getMessage());
        }
    }
    
    public function destroy($id)
    {
        Log::info('Starting test deletion process', [
            'test_id' => $id,
            'admin_id' => auth()->id()
        ]);
    
        try {
            $test = Test::with(['questions', 'invitation'])->findOrFail($id);
            
            Log::info('Found test for deletion', [
                'test_id' => $id,
                'question_count' => $test->questions->count(),
                'has_invitation' => $test->invitation ? true : false
            ]);
    
            // Get all question IDs
            $questionIds = $test->questions->pluck('id')->toArray();
    
            // Log related data counts before deletion
            $relatedCounts = [
                'choices' => QuestionChoice::whereIn('question_id', $questionIds)->count(),
                'media' => QuestionMedia::whereIn('question_id', $questionIds)->count(),
                'questions' => count($questionIds)
            ];
    
            Log::info('Deleting related data', [
                'test_id' => $id,
                'related_counts' => $relatedCounts
            ]);
    
            // Delete related data
            QuestionChoice::whereIn('question_id', $questionIds)->delete();
            Log::info('Question choices deleted', ['test_id' => $id]);
    
            QuestionMedia::whereIn('question_id', $questionIds)->delete();
            Log::info('Question media deleted', ['test_id' => $id]);
    
            Question::whereIn('id', $questionIds)->delete();
            Log::info('Questions deleted', ['test_id' => $id]);
    
            // Delete invitation if exists
            if ($test->invitation) {
                $test->invitation->delete();
                Log::info('Test invitation deleted', ['test_id' => $id]);
            }
            
            // Delete the test
            $test->delete();
            Log::info('Test deleted successfully', ['test_id' => $id]);
    
            return redirect()->route('tests.index')
                ->with('success', 'Test and all associated data deleted successfully!');
    
        } catch (\Exception $e) {
            Log::error('Error in test deletion process', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth()->id()
            ]);
    
            return redirect()->back()
                ->with('error', 'Error deleting test: ' . $e->getMessage());
        }
    }

    public function invite($id){
        return view('tests.invite', compact('id'));
    }

    protected function getQuestionsFromExcel($file, $test)
    {
        Log::info('Starting Excel import', [
            'file_name' => $file->getClientOriginalName()
        ]);
    
        try {
            $import = new QuestionsImport($test);
            // Use toCollection instead of import
            $collection = Excel::toCollection($import, $file);
            
            // Get the first sheet's data and convert to array
            $data = $collection->first()->toArray();
    
            Log::info('Excel import completed', [
                'test_id' => $test->id,
                'row_count' => count($data),
                'sample_data' => !empty($data) ? $data[0] : null
            ]);
    
            return $data;
    
        } catch (\Exception $e) {
            Log::error('Excel import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function show($id)
    {
        if (Auth::guard('web')->check()) {
            $test = Test::with(['questions.choices', 'questions.media', 'admin'])->findOrFail($id);
            $questions = $test->questions()
            ->with(['choices', 'media'])
            ->orderBy('id', 'asc')
            ->get();

            Log::info('Test questions loaded', [
                'test_id' => $id,
                'question_count' => $questions->count(),
                'has_media' => $questions->filter->media->isNotEmpty(),
                'has_choices' => $questions->filter->choices->isNotEmpty(),
            ]);
            
            return view('tests.show', compact('test', 'questions'));

        } elseif (Auth::guard('candidate')->check()) {
            try {

                $invitation = Invitation::with(['test.questions.choices', 'test.questions.media'])
                    ->where('test_id', $id)
                    ->where('expiration_date', '>', now())
                    ->firstOrFail();
                
                $test = $invitation->test;
                $questions = $test->questions;
                
                $candidate = Auth::guard('candidate')->user();
                
                $testAttempt = $candidate->tests()->find($id);
        
                $isTestStarted = $testAttempt && $testAttempt->pivot->started_at;
                $isTestCompleted = $testAttempt && $testAttempt->pivot->completed_at;
                $isInvitationExpired = $invitation->expiration_date < now();
        
                $remainingTime = null;
                $testSession = session('test_session');
                
                if ($isTestStarted && $testSession) {
                    $endTime = Carbon::parse($testSession['end_time']);
                    $remainingTime = max(0, now()->diffInSeconds($endTime, false));
                }
        
                return view('tests.show', compact('test', 'isTestStarted', 'questions',
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
        
            $existingAttempt = $candidate->tests()->wherePivot('test_id', $id)->exists();
            if (!$existingAttempt) {
                $candidate->tests()->attach($id, [
                    'started_at' => $startTime,
                ]);
            }
        
        } else {
            $startTime = Carbon::parse($testSession['start_time']);
            $endTime = $startTime->copy()->addMinutes($test->duration);
        
            if (!isset($testSession['end_time']) || !Carbon::hasFormat($testSession['end_time'], 'Y-m-d H:i:s')) {
                $testSession['end_time'] = $endTime->toDateTimeString();
            } else {
                $endTime = Carbon::parse($testSession['end_time']);
            }
        }

        $testAttempt = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->first();

        if (!$testAttempt) {
            // Create a new pivot record if it doesn't exist
            $candidate->tests()->attach($id, [
                'started_at' => now(),
            ]);

            // Fetch the newly created record
            $testAttempt = $candidate->tests()
                ->wherePivot('test_id', $id)
                ->first();
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
        $candidate = Auth::guard('candidate')->user();
        Log::info('Processing next question', ['test_id' => $id]);

        $testAttempt = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->first();

        if (!$testAttempt) {
            Log::error('Test attempt not found', ['test_id' => $id, 'candidate_id' => $candidate->id]);
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Test attempt not found.');
        }

        Log::info('Test attempt found', [
            'test_id' => $testAttempt->pivot->test_id,
            'candidate_id' => $candidate->id,
            'started_at' => $testAttempt->pivot->started_at
        ]);

        if ($this->checkTimeUp($testAttempt)) {
            Log::info('Time is up during next question', ['test_id' => $id]);
            return $this->submitTest($request, $id);
        }

        $test = Test::with(['questions.choices', 'questions.media'])->findOrFail($id);
        $questions = $test->questions;

        Log::info('Loaded test questions', [
            'test_id' => $id,
            'question_count' => $questions->count()
        ]);
        Log::info('Request data before validation', $request->all());


        $request->validate([
            'current_index' => 'required|numeric',
            'answer' => 'nullable|exists:question_choices,id', 
        ]);

        Log::info('hi');

        $testSession = session('test_session');
        if (!$testSession || $testSession['test_id'] != $id) {
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid test session.');
        }

        $currentIndex = $request->input('current_index');
        $choiceId = intval($request->input('answer', null)); 
        $answerText = QuestionChoice::findOrFail($choiceId)->choice_text ?? null;
        $currentQuestion = $questions[$request->input('current_index')];

        if (!$currentQuestion) {
            Log::error('Invalid question index', [
                'current_index' => $currentIndex,
                'test_id' => $id
            ]);
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid question index.');
        }

        Log::info('Processing answer', [
            'candidate_id' => $candidate->id,
            'question_id' => $currentQuestion->id,
            'choiceId' => $choiceId ?? 'Unanswered '
        ]);

        $testSession['answers'][$currentIndex] = $choiceId ?? 'Unanswered ';
        
        try {
            Answer::create([
                'candidate_id' => $candidate->id,
                'question_id' => $currentQuestion->id,
                'answer_text' => $answerText, 
            ]);
            Log::info('Answer saved successfully', [
                'candidate_id' => $candidate->id,
                'question_id' => $currentQuestion->id,
                'answer_text' => $answerText ?? 'Unanswered ',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save answer', [
                'candidate_id' => $candidate->id,
                'question_id' => $currentQuestion->id,
                'error' => $e->getMessage(),
            ]);
        }

        $nextIndex = $currentIndex + 1;

        Log::info('Moving to next question', [
            'test_id' => $id,
            'current_index' => $currentIndex,
            'next_index' => $nextIndex
        ]);

        if ($nextIndex >= $questions->count()) {
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
    
            Log::info('hi');
    
            $testSession = session('test_session');
            if (!$testSession || $testSession['test_id'] != $id) {
                Log::error('Invalid test session during submission', [
                    'test_id' => $id,
                    'session_data' => $testSession
                ]);
                throw new \Exception('Invalid test session');
            }
    
            $currentIndex = $request->input('current_index');
            $choiceId = intval($request->input('answer', null)); 
            $answerText = QuestionChoice::findOrFail($choiceId)->choice_text ?? null;
            $currentQuestion = $questions[$currentIndex];
      
            
            if (!$currentQuestion) {
                Log::error('Invalid question index', [
                    'current_index' => $currentIndex,
                    'test_id' => $id
                ]);
                return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid question index.');
            }
            
            Log::info('Processing answer', [
                'current_index' => $currentIndex,
                'candidate_id' => $candidate->id,
                'question_id' => $currentQuestion->id,
                'choiceId' => $choiceId ?? 'Unanswered '
            ]);

            // try {
            //     Answer::create([
            //         'candidate_id' => $candidate->id,
            //         'question_id' => $currentQuestion->id,
            //         'answer_text' => $answerText, 
            //     ]);
            //     Log::info('Answer saved successfully', [
            //         'candidate_id' => $candidate->id,
            //         'question_id' => $currentQuestion->id,
            //         'answer_text' => $answerText ?? 'Unanswered ',
            //     ]);
            // } catch (\Exception $e) {
            //     Log::error('Failed to save answer', [
            //         'candidate_id' => $candidate->id,
            //         'question_id' => $currentQuestion->id,
            //         'error' => $e->getMessage(),
            //     ]);
            // }

            // $testSession['answers'][$currentIndex] = $choiceId ?? 'Unanswered ';

            // Calculate the score efficiently
            $answers = Answer::where('candidate_id', $candidate->id)
                ->whereIn('question_id', $questions->pluck('id'))
                ->get();

            $score = 0;
            foreach ($questions as $question) {
                if ($question->question_type === 'MCQ') {
                    $correctChoice = $question->choices->firstWhere('is_correct', true);
                    $userAnswer = $answers->firstWhere('question_id', $question->id);

                    if ($correctChoice && $userAnswer && $userAnswer->answer_text == $correctChoice->choice_text) {
                        $score++;
                    }
                }
            }
    
            $now = now();
    
            $candidate->tests()->updateExistingPivot($id, [
                'completed_at' => $now,
                'score' => $score,
            ]);
            Log::info('Test completed successfully', [
                'test_id' => $id,
                'candidate_id' => $candidate->id,
                'score' => $score
            ]);
    
            // Clear the session
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
        $test = Test::with(['questions.choices'])->findOrFail($test->id);
        $questions = $test->questions;
        $candidate = Auth::guard('candidate')->user();

        $answers = Answer::where('candidate_id', $candidate->id)
        ->whereIn('question_id', $questions->pluck('id')) // Ensure answers belong to test questions
        ->get();
        Log::info('answers', $answers->toArray());
        
        
        $score = 0;
        foreach ($questions as $question) {
            if ($question->question_type === 'MCQ') {
                $correctChoice = $question->choices->firstWhere('is_correct', true);
                $userAnswer = $answers->firstWhere('question_id', $question->id);
                
                Log::info($userAnswer->answer_text ?? []);
                Log::info($correctChoice->choice_text ?? []);
                if ($correctChoice && $userAnswer && $userAnswer->answer_text == $correctChoice->choice_text) {
                    $score++;
                }
            }
        }
        
        $test_pivot = $candidate->tests()->where('test_id', $test->id)->first()->pivot;
        $started_at = Carbon::parse($test_pivot->started_at);
        $completed_at = $started_at->addMinutes($test->duration);
        
        
        $candidate->tests()->updateExistingPivot($test->id, [
            'completed_at' => $completed_at,
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
            // Get the invitation
            $invitation = $this->validateSession();

            return view('candidate.dashboard', compact('test', 'invitation'))
                ->with('error', 'No test attempt found.');
        
        }

        $startTime = $testAttempt->pivot->started_at;
        $endTime = Carbon::parse($startTime)->addMinutes($test->duration);
        $isExpired = $endTime->isPast();

        return view('tests.result', [
            'test' => $test,
            'candidate' => $candidate,
            'testAttempt' => $testAttempt,
            'questions' => $questions,
            'isExpired' => $isExpired
        ]);
    }
    
    private function validateSession()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');

        if (!$invitationLink || !$candidateEmail) {
            return null;
        }

        return Invitation::where('invitation_link', $invitationLink)
            ->where('expiration_date', '>', now())
            ->first();
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