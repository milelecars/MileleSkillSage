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

    public function update(Request $request, Test $test)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'questions_csv' => 'nullable|file|mimes:csv,xlsx,txt',
        ]);

        // Update the test attributes
        $test->update($validatedData);

        // Handle file upload if a new CSV is provided
        if ($request->hasFile('questions_csv')) {
            $path = $request->file('questions_csv')->store('csvs'); // Store the CSV file and get the path
            $test->csv_file_path = $path; // Save the path in the database
            $test->save(); // Save the changes to the test

            $this->processQuestionsCSV($path, $test); // Process the CSV to create questions
        }

        return redirect()->route('tests.show', $test)->with('success', 'Test updated successfully.');
    }

   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'file' => 'required|file|mimes:xlsx,csv,json',
        ]);

        // Store the test first
        $test = Test::create([
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
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

            return redirect()->route('tests.index')->with('success', 'Test created and questions processed successfully.');
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
        $test->delete();

        return redirect()->route('tests.index')
            ->with('success', 'Test and all associated data deleted successfully!');
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