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

class TestController extends Controller
{
    public function index()
    {
        $tests = Test::all();
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
            'description' => 'required|string',
            'file' => 'sometimes|file|mimes:xlsx,csv,json',
        ]);
    
        // Update the test attributes
        $test->update([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
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

    public function show($id)
    {
        $test = Test::with('invitation')->findOrFail($id);
        $questions = [];
        
        if ($test->questions_file_path) {
            $filePath = storage_path('app/public/' . $test->questions_file_path);
            $questions = Excel::toArray(new QuestionsImport($test), $filePath);
            $questions = $questions[0] ?? []; // Assuming we're only interested in the first sheet
        }
        
        return view('tests.show', compact('test', 'questions'));
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


    public function startTest(Request $request, $id)
    {
        
        if (!Auth::guard('candidate')->check()) {
            return redirect()->route('invitation.candidate-auth')->with('error', 'Unauthorized access to the test.');
        }
        
        // Find the test by ID
        $test = Test::findOrFail($id);
    
        // Store the test ID in the session (only if needed)
        $request->session()->put('test_id', $test->id);
    
        // Display the test for the candidate
        return view('tests.start', compact('test'));
    }    
}