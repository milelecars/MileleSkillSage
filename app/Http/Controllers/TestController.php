<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\Candidate;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\QuestionMedia;
use App\Models\Invitation;
use App\Models\Department;
use App\Models\FlagType;
use App\Models\CandidateFlag;
use App\Models\Answer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\QuestionsImport;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\TestReportService;


class TestController extends Controller
{
    protected $testReportService;

    public function __construct(TestReportService $testReportService)
    {
        $this->testReportService = $testReportService;
    }
    
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Test::query();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        $tests = $query->orderBy('created_at', 'desc')
                    ->paginate(10);
        
        return view('tests.index', compact('tests', 'search'));
    }

    public function create()
    {
        return view('tests.create');
    }

    public function store(Request $request)
    {
        Log::info('Starting test creation process', [
            'admin_id' => auth()->id(),
            'request_data' => $request->except(['file'])
        ]);
    
        try {
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
    
            Log::info('Test created successfully', [
                'test_id' => $test->id,
                'test_data' => $test->toArray()
            ]);
    
            $urlParts = explode('/', $validatedData['invitation_link']);
            $invitationToken = end($urlParts);
    
            Log::info('Parsed invitation link', [
                'url_parts' => $urlParts,
                'token' => $invitationToken
            ]);
    
            $invitation = Invitation::create([
                'test_id' => $test->id,
                'invited_emails' => json_encode([]),
                'expiration_date' => now()->addYear(),
                'invitation_token' => $invitationToken,
                'invitation_link' => $validatedData['invitation_link'],
            ]);
    
            Log::info('Invitation created', [
                'invitation_id' => $invitation->id,
                'invitation_data' => $invitation->toArray()
            ]);
    
            if (!$request->hasFile('file')) {
                Log::warning('No file uploaded', ['test_id' => $test->id]);
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
            if (!in_array($extension, ['xlsx', 'csv'])) {
                Log::error('Unsupported file type', ['extension' => $extension]);
                return redirect()->back()->with('error', 'Unsupported file type.');
            }
    
            // Parse questions
            $questions = $this->getQuestionsFromExcel($file, $test);
            if (empty($questions)) {
                throw new \Exception('No valid questions found in the file.');
            }
    
            // Create questions and related data
            foreach ($questions as $q) {
                Log::info('Processing question', ['question' => $q]);
    
                // Normalize data to avoid case-sensitivity issues
                $q = array_change_key_case($q, CASE_LOWER);
    
                if ($q['type'] === 'MCQ') {
                    $question = Question::create([
                        'test_id' => $test->id,
                        'question_text' => $q['question'],
                        'question_type' => 'MCQ',
                    ]);
    
                    $choices = [
                        'a' => $q['choice_a'] ?? null,
                        'b' => $q['choice_b'] ?? null,
                        'c' => $q['choice_c'] ?? null,
                        'd' => $q['choice_d'] ?? null
                    ];
    
                    foreach ($choices as $key => $text) {
                        if (!empty($text)) {
                            QuestionChoice::create([
                                'question_id' => $question->id,
                                'choice_text' => $text,
                                'is_correct' => strtoupper($key) === strtoupper($q['answer'])
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
                } elseif ($q['type'] === 'LSQ') {
                    $question = Question::create([
                        'test_id' => $test->id,
                        'question_text' => $q['question'],
                        'question_type' => 'LSQ',
                        'category' => $q['category'] ?? null,
                        'reverse' => isset($q['reverse']) ? boolval($q['reverse']) : false,
                        'red_flag' => isset($q['red_flag']) ? boolval($q['red_flag']) : false,

                    ]);
                    Log::info('Quest', [
                        'category' => $q['category'] ?? null,
                        'reverse' => isset($q['reverse']) ? boolval($q['reverse']) : false,
                        'red_flag' => isset($q['red_flag']) ? boolval($q['red_flag']) : false,

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
            Log::error('Error in test creation process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth()->id()
            ]);
    
            return redirect()->back()->with('error', 'Error creating test: ' . $e->getMessage());
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
        Log::info('Starting test soft deletion process', [
            'test_id' => $id,
            'admin_id' => auth()->id()
        ]);

        try {
            $test = Test::findOrFail($id);
            
            // Update the deleted_by first
            $test->update([
                'deleted_by' => auth()->id()
            ]);
            
            // Then call delete() which will set deleted_at automatically
            $test->delete();

            Log::info('Test soft deleted successfully', [
                'test_id' => $id,
                'deleted_at' => $test->deleted_at,
                'deleted_by' => $test->deleted_by
            ]);

            // Update invitation status if exists
            if ($test->invitation) {
                $test->invitation->update([
                    'status' => 'inactive'
                ]);
            }

            return redirect()->route('tests.index')
                ->with('success', 'Test has been archived successfully!');

        } catch (\Exception $e) {
            Log::error('Error in test deletion process', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error archiving test: ' . $e->getMessage());
        }
    }
    
    public function archived()
    {
        $archivedTests = Test::with(['admin', 'deletedBy'])
                             ->onlyTrashed()
                             ->get();
    
        return view('tests.archived', compact('archivedTests'));
    }
    
    public function restore($id)
    {
        try {
            $test = Test::withTrashed()->where('id', $id)->first();
            Test::withTrashed()->where('id', $id)->restore();
    
            if ($test->invitation) {
                $test->invitation->update([
                    'status' => 'active'
                ]);
            }
    
            Log::info('Test restored successfully', [
                'test_id' => $id,
                'admin_id' => auth()->id()
            ]);
    
            return redirect()->route('tests.index')
                ->with('success', 'Test has been restored successfully!');
    
        } catch (\Exception $e) {
            Log::error('Error restoring test', [
                'test_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()->back()
                ->with('error', 'Error restoring test: ' . $e->getMessage());
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
            Excel::import($import, $file);
            
            // Get the sanitized data from the import
            $data = $import->getCleanedData();
            
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
    
    public function setup($id)
    {
        $candidate = Auth::guard('candidate')->user();
        if (!$candidate) {
            return redirect()->route('invitation.candidate-auth');
        }
    
        $test = Test::with('questions')->findOrFail($id);
        
        $hasAccess = $candidate->tests()->where('test_id', $id)->exists() || 
                    Invitation::where('test_id', $id)
                            ->whereJsonContains('invited_emails->invites', ['email' => $candidate->email])
                            ->exists();
        
        if (!$hasAccess) {
            return redirect()->route('candidate.dashboard')
                            ->with('error', 'You do not have access to this test.');
        }

        $existingAttempt = $candidate->tests()->where('test_id', $id)->first();

        if (!$existingAttempt) {
            $candidate->tests()->attach($id, [
                'status' => 'not started'
            ]);
        }
    
        $testAttempt = $candidate->tests()
                                ->where('test_id', $id)
                                ->first();
    
        $invitation = $this->validateSession();
    
        if ($testAttempt && in_array($testAttempt->pivot->status, ['completed', 'accepted', 'rejected'])) {
            return redirect()->route('tests.result', $id)
                            ->with('info', 'This test has already been completed.');
        }
        
        return view('tests.setup', compact('test', 'testAttempt', 'invitation'));
    }

    public function show($id)
    {
        if (Auth::guard('web')->check()) {
            $test = Test::with([
                'questions' => function ($query) {
                    $query->with(['choices', 'media'])->where('question_type', 'MCQ'); // Only MCQ gets choices & media
                },
                'admin'
            ])->findOrFail($id);
            
            $questions = $test->questions()
            ->with(['choices', 'media'])
            ->orderBy('id', 'asc')
            ->get();
            $hasMCQ = $questions->contains('question_type', 'MCQ');
            $hasLSQ = $questions->contains('question_type', 'LSQ');

            Log::info('Test questions loaded', [
                'test_id' => $id,
                'question_count' => $questions->count(),
                'has_media' => $questions->filter->media->isNotEmpty(),
                'has_choices' => $questions->filter->choices->isNotEmpty(),
            ]);
            
            return view('tests.show', compact('test', 'questions', 'hasMCQ', 'hasLSQ'));

        } elseif (Auth::guard('candidate')->check()) {
            try {
                $candidate = Auth::guard('candidate')->user();
                
                $invitation = Invitation::with(['test.questions.choices', 'test.questions.media'])
                    ->where('test_id', $id)
                    ->where('expiration_date', '>', now())
                    ->firstOrFail();
                
                // Check candidate's individual deadline
                $invites = $invitation->invited_emails['invites'] ?? [];
                $candidateInvite = collect($invites)->firstWhere('email', $candidate->email);
                
                if ($candidateInvite && now()->greaterThan(new Carbon($candidateInvite['deadline']))) {
                    return redirect()->route('login')
                        ->with('error', 'Your test deadline has expired.');
                }
                
                $test = $invitation->test;
                $questions = $test->questions;
                $hasMCQ = $questions->contains('question_type', 'MCQ');
                $hasLSQ = $questions->contains('question_type', 'LSQ');
                
                $testAttempt = $candidate->tests()->find($id);
        
                $isTestStarted = $testAttempt && $testAttempt->pivot->status == "in progress";
                $isTestCompleted = $testAttempt && $testAttempt->pivot->status == "completed";
                $isInvitationExpired = $invitation->expiration_date < now();
        
                $remainingTime = null;
                $testSession = session('test_session');
                
                if ($isTestStarted && $testSession) {
                    $endTime = Carbon::parse($testSession['end_time']);
                    $remainingTime = max(0, now()->diffInSeconds($endTime, false));
                }

                // Test Preview
                $questionsExplained = [
                    'The (B) answer is the right one, because the word "abuse" means practically the same thing as the word MALTREAT. For each question of this kind, you are to decide which of the four possible answers means most nearly the same thing as the capitalized word in the sentence.',
                    'The (D) answer is the right one for this question, because the word "reached" most nearly means the same thing as ATTAINED. Notice that it is necessary to read all four choices. You are to choose the best answer, not just a possible answer.',
                    'The answer is (A).',
                    'For this question, the (C) answer is the right one, because 102 plus 120 plus 50 makes 272.',
                    'The (A) answer is the right one, because 7x9=63.',
                    'There are 3 boxes in the pile. So the (C) answer is the right one.',
                    'The right answer is 4 boxes. Only 3 boxes show in the first picture. But the other picture shows that there is one more box which was covered up in the first picture. The hidden box has to be counted too, making 4 boxes altogether. So the (A) answer is the right one.',
                    'You can see that the right answer is 5 boxes. So the (B) answer is the right one.'
                ];
        
                return view('tests.show', compact('test', 'isTestStarted', 'questions', 'hasMCQ', 'hasLSQ', 'questionsExplained',
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

    public function saveScreenshot(Request $request)
    {
    
        $candidate = Auth::guard('candidate')->user();
        $testSession = session('test_session');
    
        if (!$testSession) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save screenshot: No active test session found'
            ], 500);
        }
    
        try {
            $request->validate([
                'screenshot' => 'required|string',
                'timestamp' => 'required|date'
            ]);
    
            $candidateTest = DB::table('candidate_test')
                ->select('candidate_id', 'test_id')
                ->where('candidate_id', $candidate->id)
                ->where('test_id', $testSession['test_id'])
                ->first();
    
            if (!$candidateTest) {
                throw new \Exception('No active test attempt found');
            }
    
            $directory = 'screenshots/test' . $testSession['test_id'] . '/candidate' . $candidate->id;
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
    
            $filename = $directory . '/' . now()->format('Y-m-d_H-i-s') . '.jpg';
    
            $image = str_replace('data:image/jpeg;base64,', '', $request->screenshot);
            $image = str_replace(' ', '+', $image);
            $imageBinary = base64_decode($image);
    
            Storage::put($filename, $imageBinary);

            DB::table('candidate_test_screenshots')->insert([
                'candidate_id' => $candidate->id,
                'test_id' => $testSession['test_id'],
                'screenshot_path' => $filename,
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            Log::info('Screenshot saved', [
                'test_id' => $testSession['test_id'],
                'candidate_id' => $candidate->id,
                'filename' => $filename
            ]);
    
            return response()->json([
                'success' => true,
                'filename' => $filename
            ]);
    
        } catch (\Exception $e) {
            Log::error('Screenshot save error:', [
                'error' => $e->getMessage(),
                'test_id' => $testSession['test_id'] ?? null,
                'candidate_id' => $candidate->id,
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to save screenshot: ' . $e->getMessage()
            ], 500);
        }
    }

    public function startTest(Request $request, $id)
    {
        if (!Auth::guard('candidate')->check()) {
            return redirect()->route('invitation.candidate-auth')
                ->with('error', 'Unauthorized access to the test.');
        }
    
        if ($request->isMethod('post')) {
            $request->validate([
                'agreement' => 'required|accepted',
            ], [
                'agreement.required' => 'You must agree to the terms and guidelines to proceed.',
                'agreement.accepted' => 'You must agree to the terms and guidelines to proceed.'
            ]);
        }
    
        $candidate = Auth::guard('candidate')->user();
        $test = Test::with([
            'questions.choices' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_choices.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            },
            'questions.media' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_media.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            }
        ])->findOrFail($id);
                
        if($test->title == "General Mental Ability (GMA)"){
            $questions = $test->questions()
            ->with(['choices', 'media'])
            ->whereRaw('id > (SELECT id FROM questions WHERE test_id = ? ORDER BY id LIMIT 8,1)', [$test->id])
            ->get();
        }else{
            
            $questions = $test->questions;
        }
    
        $isCompleted = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->where('candidate_test.status', 'completed')
            ->exists();
    
        if ($isCompleted) {
            return redirect()->route('tests.result', ['id' => $id])
                ->with('info', 'You have already completed this test.');
        }

        $testAttempt = $candidate->tests()
            ->wherePivot('test_id', $id)
            ->first();

        $testSession = session('test_session', []);
        $endTime = null;
    
        if (!$testAttempt) {
            $candidate->tests()->attach($id, [
                'started_at' => now(),
                'status' => 'in progress'
            ]);
            Log::info('New test attempt created', [
                'test_id' => $test->id,
                'candidate_id' => $candidate->id
            ]);

        } else {
            if ($testAttempt->pivot->status !== 'in progress') {
                $candidate->tests()->updateExistingPivot($id, [
                    'status' => 'in progress',
                    'updated_at' => now()
                ]);
                Log::info('Test attempt status updated to "in progress"', [
                    'test_id' => $test->id,
                    'candidate_id' => $candidate->id
                ]);
            }
        }

        if (!isset($testSession['test_id']) || $testSession['test_id'] != $id) {
            $startTime = now();
            $endTime = $startTime->copy()->addMinutes($test->duration);
            $allQuestionIds = $questions->pluck('id')->toArray();

            // Randomize LSQ questions
            $hasLSQ = $test->questions->contains('question_type', 'LSQ');
            if ($hasLSQ) {
                shuffle($allQuestionIds);
            }

            $testSession = [
                'test_id' => $test->id,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'current_question' => 0,
                'answers' => [],
                'question_order' => $allQuestionIds,
                'total_questions' => count($allQuestionIds)
            ];
            session(['test_session' => $testSession]);        
            
            $existingAttempt = $candidate->tests()->wherePivot('test_id', $id)->first();
            $candidate->tests()->updateExistingPivot($id, [
                'started_at' => $startTime,
                'status' => 'in progress'
            ]);
            
        } else {
            $startTime = Carbon::parse($testSession['start_time']);
            $endTime = $startTime->copy()->addMinutes($test->duration);
            
            if (!isset($testSession['end_time']) || !Carbon::hasFormat($testSession['end_time'], 'Y-m-d H:i:s')) {
                $testSession['end_time'] = $endTime->toDateTimeString();
            } else {
                $endTime = Carbon::parse($testSession['end_time']);
            }
            
            if (!isset($testSession['question_order'])) {
                $allQuestionIds = $questions->pluck('id')->toArray();

                if ($test->questions->contains('question_type', 'LSQ')) {
                    shuffle($allQuestionIds);
                }

                $testSession['question_order'] = $allQuestionIds;
                $testSession['total_questions'] = count($allQuestionIds);
                session(['test_session' => $testSession]);

                Log::warning('question_order was missing in session and rebuilt', [
                    'candidate_id' => $candidate->id,
                    'test_id' => $test->id
                ]);
            }

        }        
    
        $questions = $questions->sortBy(function($question) use ($testSession) {
            return array_search($question->id, $testSession['question_order']);
        })->values();
    
        if (!isset($testSession['current_question']) || $testSession['current_question'] >= count($questions)) {
            $testSession['current_question'] = 0;
        }
            
        if (now()->gt($endTime)) {
            return $this->handleTimeout($test);
        }
        $flagTypes = FlagType::all();
        session(['test_session' => $testSession]);

        $currentQuestionIndex = $testSession['current_question'];

        return view('tests.start', compact('test', 'candidate', 'questions', 'currentQuestionIndex', 'flagTypes'));
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

        if ($this->checkTimeUp($testAttempt)) {
            Log::info('Time is up during next question', ['test_id' => $id]);
            return $this->submitTest($request, $id);
        }

        $test = Test::with([
            'questions.choices' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_choices.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            },
            'questions.media' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_media.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            }
        ])->findOrFail($id);

        $testSession = session('test_session');

        if (!$testSession || $testSession['test_id'] != $id) {
            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid test session.');
        }

        $currentIndex = intval($request->input('current_index', 0));
        $allQuestionIds = $testSession['question_order'];

        if ($test->title == "General Mental Ability (GMA)") {
            $questions = $test->questions()
                ->with(['choices', 'media'])
                ->whereRaw('id > (SELECT id FROM questions WHERE test_id = ? ORDER BY id LIMIT 8,1)', [$test->id])
                ->get();
        } else {
            $questions = $test->questions;
        }

        $questions = $questions->sortBy(function($question) use ($allQuestionIds) {
            return array_search($question->id, $allQuestionIds);
        })->values();

        // Ensure questions array is not empty
        if ($questions->isEmpty() || !isset($questions[$currentIndex])) {
            Log::error('Invalid question index or no questions found', [
                'current_index' => $currentIndex,
                'test_id' => $id
            ]);

            return redirect()->route('tests.start', ['id' => $id])
                ->with('error', 'Invalid question index or no questions found.');
        }

        
        if ($currentIndex >= count($questions)) {
            return $this->submitTest($request, $id);
        }


        $currentQuestion = $questions[$currentIndex];
        Log::info('Request Data', $request->all());

        // Check the current question type
        Log::info('Processing Question', [
            'question_id' => $currentQuestion->id,
            'question_type' => $currentQuestion->question_type
        ]);

        $validationRules = [
            'current_index' => 'required|numeric',
        ];

        if ($currentQuestion->question_type === 'MCQ') {
            $validationRules['answer'] = 'nullable|exists:question_choices,id';
        } elseif ($currentQuestion->question_type === 'LSQ') {
            $validationRules['lsq_answers'] = 'required|array';
            $validationRules["lsq_answers.{$currentQuestion->id}"] = 'integer|min:1|max:5';
        }

        $request->validate($validationRules);

        // Save MCQ Answer
        if ($currentQuestion->question_type === 'MCQ' && $request->filled('answer')) {
            $choiceId = intval($request->input('answer'));
            $answerText = QuestionChoice::findOrFail($choiceId)->choice_text;

            try {
                Answer::create([
                    'candidate_id' => $candidate->id,
                    'test_id' => $test->id,
                    'question_id' => $currentQuestion->id,  
                    'answer_text' => $answerText, 
                ]);
                Log::info('Answer saved successfully', [
                    'candidate_id' => $candidate->id,
                    'test_id' => $test->id,
                    'question_id' => $currentQuestion->id,
                    'answer_text' => $answerText,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to save answer', [
                    'candidate_id' => $candidate->id,
                    'test_id' => $test->id,
                    'question_id' => $currentQuestion->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Save LSQ Answer
        if ($currentQuestion->question_type === 'LSQ' && $request->has("lsq_answers.{$currentQuestion->id}")) {
            $lsqValue = intval($request->input("lsq_answers.{$currentQuestion->id}"));

            Answer::updateOrCreate(
                [
                    'candidate_id' => $candidate->id,
                    'test_id' => $test->id,
                    'question_id' => $currentQuestion->id,
                ],
                ['answer_text' => $lsqValue]
            );
            Log::info('LSQ Answer saved', [
                'candidate_id' => $candidate->id,
                'test_id' => $test->id,
                'question_id' => $currentQuestion->id,
                'answer_text' => $lsqValue
            ]);
        }

        // Move to the next question
        $nextIndex = $currentIndex + 1;

        if ($nextIndex >= $questions->count()) {
            return $this->submitTest($request, $id);
        }

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
            $testSession = session('test_session');

            if (!$testSession || $testSession['test_id'] != $id) {
                return redirect()->route('tests.start', ['id' => $id])
                    ->with('error', 'Invalid test session.');
            }

            $test = Test::with([
                'questions.choices' => function ($query) {
                    $query->whereExists(function ($subQuery) {
                        $subQuery->selectRaw(1)->from('questions')
                            ->whereColumn('questions.id', 'question_choices.question_id')
                            ->where('questions.question_type', 'MCQ');
                    });
                },
                'questions.media' => function ($query) {
                    $query->whereExists(function ($subQuery) {
                        $subQuery->selectRaw(1)->from('questions')
                            ->whereColumn('questions.id', 'question_media.question_id')
                            ->where('questions.question_type', 'MCQ');
                    });
                }
            ])->findOrFail($id);

            // For GMA test, get questions after skipping first 8
            if($test->title == "General Mental Ability (GMA)") {
                $questions = $test->questions()
                    ->with(['choices', 'media'])
                    ->skip(8)
                    ->take(PHP_INT_MAX)
                    ->get();
            } else {
                $questions = $test->questions;
            }

            $allQuestionIds = $testSession['question_order'];
            $questions = $questions->sortBy(function ($q) use ($allQuestionIds) {
                return array_search($q->id, $allQuestionIds);
            })->values();

            Log::info('Total Questions After Filtering:', [
                'test_id' => $id,
                'candidate_id' => $candidate->id,
                'question_count' => $questions->count()
            ]);

            // Ensure test attempt exists
            $testAttempt = $candidate->tests()->wherePivot('test_id', $id)->first();
            if (!$testAttempt) {
                Log::error('Test attempt not found', ['test_id' => $id, 'candidate_id' => $candidate->id]);
                return redirect()->route('tests.start', ['id' => $id])
                    ->with('error', 'Test attempt not found.');
            }

            if ($testAttempt->pivot->status === 'completed') {
                return redirect()->route('tests.result', ['id' => $id])
                    ->with('info', 'Test was already submitted.');
            }

            // Load all saved answers
            $answers = Answer::where('candidate_id', $candidate->id)
                ->where('test_id', $test->id)
                ->whereIn('question_id', $questions->pluck('id'))
                ->get();

            // Score MCQs
            $correct_answers = 0;
            $wrong_answers = 0;
            $unanswered = 0;

            foreach ($questions as $question) {
                if ($question->question_type !== 'MCQ') break;

                $correctChoice = $question->choices->firstWhere('is_correct', true);
                $userAnswer = $answers->firstWhere('question_id', $question->id);

                if ($correctChoice) {
                    if (!$userAnswer) {
                        $unanswered++;
                    } elseif ($userAnswer->answer_text == $correctChoice->choice_text) {
                        $correct_answers++;
                    } else {
                        $wrong_answers++;
                    }
                }
            }

            $totalQuestions = $test->title === "General Mental Ability (GMA)"
                ? DB::table('questions')->where('test_id', $test->id)->count() - 8
                : DB::table('questions')->where('test_id', $test->id)->count();

            $mcq_final_score = $this->calculateMCQScore($correct_answers, $wrong_answers, $totalQuestions);

            Log::info('MCQ Results', [
                'correct_answers' => $correct_answers,
                'wrong_answers' => $wrong_answers,
                'unanswered' => $unanswered
            ]);

            // Score LSQs
            $total_score = 0;
            $total_questions = 0;
            $red_flags = [];

            foreach ($questions as $question) {
                if ($question->question_type !== 'LSQ') continue;

                $userAnswer = $answers->firstWhere('question_id', $question->id);

                if ($userAnswer) {
                    $score = intval($userAnswer->answer_text);

                    if ($question->reverse == 1) {
                        $score = 6 - $score;
                    }

                    if ($question->red_flag == 1) {
                        $red_flags[] = [
                            'question' => $question->question_text,
                            'answer' => $userAnswer->answer_text
                        ];
                    } else {
                        $total_score += $score;
                        $total_questions++;
                    }
                }
            }

            Log::info('LSQ Score Calculated', [
                'total_score' => $total_score,
                'total_questions' => $total_questions,
                'final_score' => $total_score
            ]);

            // Finalize test attempt
            $realIP = $this->testReportService->getClientIP();
            $location = $this->testReportService->getLocationFromIP($realIP);
            Log::info("Location Data", $location);

            $started_at = Carbon::parse($testAttempt->pivot->started_at);
            $completed_at = now()->gt($started_at->copy()->addMinutes($test->duration))
                ? $started_at->copy()->addMinutes($test->duration)
                : now();

            $candidate->tests()->updateExistingPivot($id, [
                'completed_at' => $completed_at,
                'ip_address' => $realIP,
                'status' => 'completed'
            ]);

            // Save MCQ score if any MCQs exist
            if ($questions->contains(fn($q) => $q->question_type === 'MCQ')) {
                $candidate->tests()->updateExistingPivot($id, [
                    'correct_answers' => $correct_answers,
                    'wrong_answers' => $wrong_answers,
                    'score' => $mcq_final_score,
                ]);
            }

            // Save LSQ score if any LSQs exist
            if ($questions->contains(fn($q) => $q->question_type === 'LSQ')) {
                $candidate->tests()->updateExistingPivot($id, [
                    'score' => $total_score,
                    'red_flags' => $red_flags
                ]);
            }

            Log::info('Test completed successfully', [
                'test_id' => $id,
                'candidate_id' => $candidate->id,
                'red_flags' => $red_flags,
                'correct_answers' => $correct_answers,
                'wrong_answers' => $wrong_answers,
                'lsq_score' => $total_score,
                'ip_address' => $realIP,
            ]);

            $this->testReportService->generatePDF($candidate->id, $id);
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

    public function handleTimeout($test)
    {
        Log::info('Handling expired test', ['test_id' => $test->id]);

        $test = Test::with([
            'questions.choices' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_choices.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            },
            'questions.media' => function ($query) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->selectRaw(1)->from('questions')
                        ->whereColumn('questions.id', 'question_media.question_id')
                        ->where('questions.question_type', 'MCQ');
                });
            }
        ])->findOrFail($test->id);
        
        if ($test->title == "General Mental Ability (GMA)") {
            $questions = $test->questions()
                ->whereRaw('id > (SELECT id FROM questions WHERE test_id = ? ORDER BY id LIMIT 8,1)', [$test->id])
                ->get();
        } else {
            $questions = $test->questions;
        }

        $hasMCQ = $questions->contains('question_type', 'MCQ');
        $hasLSQ = $questions->contains('question_type', 'LSQ');

        $candidate = Auth::guard('candidate')->user();
        $answers = Answer::where('candidate_id', $candidate->id)
            ->where('test_id', $test->id)
            ->whereIn('question_id', $questions->pluck('id'))
            ->get();

        // For MCQ
        $correct_answers = 0;
        $wrong_answers = 0;
        $unanswered = 0;

        foreach ($questions as $question) {
            if ($question->question_type === 'MCQ') {
                $correctChoice = $question->choices->firstWhere('is_correct', true);
                $userAnswer = $answers->firstWhere(function ($answer) use ($question, $candidate, $test) {
                    return $answer->question_id == $question->id 
                           && $answer->candidate_id == $candidate->id 
                           && $answer->test_id == $test->id;
                });
                Log::info('question_id'. $question->id);
                Log::info('candidate_id'. $candidate->id);
                Log::info('test_id'. $test->id);

                Log::info($question);
                Log::info('correctChoice'. $correctChoice);
                Log::info('userAnswer'. $userAnswer);

                if ($correctChoice) {
                    if ($userAnswer) {
                        if ($userAnswer->answer_text == $correctChoice->choice_text) {
                            $correct_answers++;
                        } else {
                            $wrong_answers++;
                        }
                    } else {
                        $unanswered++;
                    }
                }
            }
        }

        if($test->title == "General Mental Ability (GMA)"){
            $totalQuestions = DB::table('questions')
                ->where('test_id', $test->id)
                ->count() - 8;
        }else{
            
            $totalQuestions = DB::table('questions')
                ->where('test_id', $test->id)
                ->count();
        }

        $mcq_final_score = $this->calculateMCQScore($correct_answers, $wrong_answers, $totalQuestions);
    

        Log::info('MCQ Results', [
            'correct_answers' => $correct_answers,
            'wrong_answers' => $wrong_answers,
            'unanswered' => $unanswered
        ]);

        // Process LSQ Results
        $total_score = 0;
        $total_questions = 0;
        $red_flags = [];

        foreach ($questions as $question) {
            if ($question->question_type !== 'LSQ') {
                continue;
            }

            $userAnswer = $answers->firstWhere('question_id', $question->id);

            if ($userAnswer) {
                $score = intval($userAnswer->answer_text);

                if ($question->reverse == 1) {
                    $score = 6 - $score; 
                }

                if ($question->red_flag == 1) {
                    $red_flags[] = [
                        'question' => $question->question_text,
                        'answer' => $userAnswer->answer_text
                    ];
                } else {
                    $total_score += $score;
                    $total_questions++;
                }
            }
        }


        $test_pivot = $candidate->tests()->where('test_id', $test->id)->first();

        $realIP = $this->testReportService->getClientIP();
        $location = $this->testReportService->getLocationFromIP($realIP);
        Log::info("location is ", $location);
        Log::info('Correct Answers: ' . $correct_answers);
        Log::info('Wrong Answers: ' . $wrong_answers);
        Log::info('Unanswered: ' . $unanswered);
        
        $test_pivot = $candidate->tests()->where('test_id', $test->id)->first();
        if ($test_pivot) {
            $test_pivot = $test_pivot->pivot;
            $started_at = Carbon::parse($test_pivot->started_at);
            $completed_at = $started_at->addMinutes($test->duration);
            Log::info('completed at: ' . $completed_at);
        } else {
            $completed_at = now();
        }

        $candidate->tests()->updateExistingPivot($test->id, [
            'ip_address' => $realIP,
            'status' => 'completed',
            'completed_at' => $completed_at
        ]);

        if ($question->question_type === 'MCQ') {
            $candidate->tests()->updateExistingPivot($test->id, [
                'correct_answers' => $correct_answers,
                'wrong_answers' => $wrong_answers,
                'score' => $mcq_final_score,

            ]);
        }
        if ($question->question_type === 'LSQ') {
            $candidate->tests()->updateExistingPivot($test->id, [
                'score' =>$total_score,
                'red_flags' => $red_flags
            ]);

        }

        $this->testReportService->generatePDF($candidate->id, $test->id);

        session()->forget('test_session');

        return redirect()->route('tests.result', ['id' => $test->id])
            ->with('warning', 'Test time has expired. Your answers have been submitted automatically.');
    }

    public function showResult($id)
    {
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

        if($test->title == "General Mental Ability (GMA)"){
            $totalQuestions = DB::table('questions')
                ->where('test_id', $test->id)
                ->count() - 8;
        }else{
            
            $totalQuestions = DB::table('questions')
                ->where('test_id', $test->id)
                ->count();
        }

        $calculatedScore = $testAttempt->pivot->score;
        $hasMCQ = $questions->contains('question_type', 'MCQ');
        $hasLSQ = $questions->contains('question_type', 'LSQ');

        return view('tests.result', [
            'test' => $test,
            'candidate' => $candidate,
            'testAttempt' => $testAttempt,
            'questions' => $questions,
            'isExpired' => $isExpired,
            'calculatedScore' => $calculatedScore,
            'hasMCQ' => $hasMCQ,
            'hasLSQ' => $hasLSQ,
            
        ]);
    }
    
    private function validateSession()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');
    
        if (!$invitationLink || !$candidateEmail) {
            return null;
        }
    
        $invitation = Invitation::where('invitation_link', $invitationLink)
            ->where('expiration_date', '>', now())
            ->first();
    
        if (!$invitation) {
            return null;
        }
    
        $invites = $invitation->invited_emails['invites'] ?? [];
        $candidateInvite = collect($invites)->firstWhere('email', $candidateEmail);
        
        if ($candidateInvite && now()->greaterThan(new Carbon($candidateInvite['deadline']))) {
            return null;
        }
    
        return $invitation;
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

    private function getQuestions($testSession)
    {
        $allQuestionIds = $testSession['question_order'];
        $questions = Question::with(['choices', 'media'])
            ->whereIn('id', $allQuestionIds)
            ->get()
            ->sortBy(function($question) use ($allQuestionIds) {
                return array_search($question->id, $allQuestionIds);
            })
            ->values();

        return $questions;
    }

    public function calculateMCQScore($correct_answers, $wrong_answers, $totalQuestions)
    {
        return $correct_answers > 0 ? round((($correct_answers - (1/3 * $wrong_answers)) / $totalQuestions) * 100, 2) : 0;
    }

    public function showSuspended($testId, Request $request)
    {
        $reason = $request->query('reason');
        $formattedReason = preg_replace('/([A-Z])/', ' $1', $reason);
        $formattedReason = strtolower($formattedReason);
        $reason = trim($formattedReason); 

        

        return view('tests.suspended', [
            'testId' => $testId,
            'reason' => $reason,
        ]);
    }

    public function requestUnsuspension($testId, Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'evidence' => 'required|file|mimes:jpg,png',
        ]);

        $candidate = Auth::guard('candidate')->user();

        DB::table('candidate_test')
            ->where('candidate_id', $candidate->id)
            ->where('test_id', $testId)
            ->update([
                'status' => 'suspended',
                'suspension_reason' => $request->description,
                'evidence_path' => $request->file('evidence')->store('unsuspension_evidence'), 
            ]);

        return redirect()->route('candidate.dashboard')
            ->with('success', 'Your unsuspension request has been submitted.');
    }
    
    public function logSuspension(Request $request)
    {
        $request->validate([
            'testId' => 'required|exists:tests,id',
            'candidateId' => 'required|exists:candidates,id',
            'violationType' => 'required|string',
        ]);

        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $request->candidateId)
            ->where('test_id', $request->testId)
            ->first();

        if ($candidateTest->is_suspended) {
            return response()->json([
                'success' => false,
                'message' => 'Test has already been suspended.',
            ], 400);
        }

        DB::table('candidate_test')
            ->where('candidate_id', $request->candidateId)
            ->where('test_id', $request->testId)
            ->update([
                'status' => 'suspended',
                'suspended_at' => now(),
                'suspension_reason' => $request->violationType,
                'is_suspended' => true,
            ]);

        Log::info('Test is suspended.');

        return response()->json([
            'success' => true,
            'message' => 'Suspension logged successfully.',
        ]);
    }

    public function getUnsuspendCount(Request $request)
    {
        $request->validate([
            'testId' => 'required|integer',
            'candidateId' => 'required|integer',
        ]);

        $candidateTest = DB::table('candidate_test')
            ->where('candidate_id', $request->candidateId)
            ->where('test_id', $request->testId)
            ->first();

        return response()->json([
            'unsuspend_count' => $candidateTest ? $candidateTest->unsuspend_count : 0,
        ]);
    }
}