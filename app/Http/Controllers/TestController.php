<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\TestInvitation;
use Illuminate\Support\Str;

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

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'invitation_link' => 'required|string|url',
        ]);

        $test = Test::create($validatedData);

        // Create test invitation
        TestInvitation::create([
            'test_id' => $test->id,
            'invitation_link' => $validatedData['invitation_link'], // Use the validated invitation link
            'email_list' => [],
            'expires_at' => now()->addDays(7),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tests.show', $test->id)
            ->with('success', 'Test created and invitation link generated successfully!');
    }



    public function show($id)
    {
        $test = Test::findOrFail($id);
        return view('tests.show', compact('test')); 
    }

    // public function edit($id)
    // {
    //     $test = Test::findOrFail($id); 
    //     return view('tests.edit', compact('test')); 
    // }

    // public function update(Request $request, $id)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'description' => 'nullable|string',
    //     ]);

    //     $test = Test::findOrFail($id); 
    //     $test->update($request->only(['name', 'description'])); 

    //     return redirect()->route('tests.index')->with('success', 'Test updated successfully.'); 
    // }

    // public function destroy($id)
    // {
    //     $test = Test::findOrFail($id); 
    //     $test->delete(); 

    //     return redirect()->route('tests.index')->with('success', 'Test deleted successfully.'); 
    // }

    public function startTest(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired invitation link.');
        }

        $invitation = TestInvitation::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        // Store session for the candidate
        $request->session()->put('candidate_id', $invitation->id);
        $request->session()->put('test_id', $invitation->test_id);

        return redirect()->route('test.take');
    }

    public function takeTest(Request $request)
    {
        if (!$request->session()->has('candidate_id')) {
            abort(401, 'Unauthorized access to the test.');
        }

        $testId = $request->session()->get('test_id');
        $test = Test::findOrFail($testId);

        // Display the test
        return view('tests.take', compact('test'));
    }
}