<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TestInvitation;
use Illuminate\Support\Facades\Auth;

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

        // Extract the token from the invitation link
        $urlParts = explode('/', $validatedData['invitation_link']);
        $invitationToken = end($urlParts); // Get the last part of the URL

        // Create the Test model without the invitation_link
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

        return redirect()->route('tests.show', $test->id)
            ->with('success', 'Test created and invitation link generated successfully!');
    }

    public function show($id)
    {
        $test = Test::with('invitation')->findOrFail($id);
        return view('tests.show', compact('test'));
    }

    public function startTest(Request $request, $id)
    {
        \Log::info('Attempting to start test. Auth status: ' . (Auth::guard('candidate')->check() ? 'Authenticated' : 'Not authenticated'));
        \Log::info('Candidate ID: ' . Auth::guard('candidate')->id());

        if (!Auth::guard('candidate')->check()) {
            \Log::info('Redirecting to candidate auth');
            return redirect()->route('invitation.candidate-auth')->with('error', 'Unauthorized access to the test.');
        }

        \Log::info('Proceeding with test start');
        
        // Find the test by ID
        $test = Test::findOrFail($id);
    
        // Store the test ID in the session (only if needed)
        $request->session()->put('test_id', $test->id);
    
        // Display the test for the candidate
        return view('tests.start', compact('test'));
    }    

    // public function takeTest(Request $request)
    // {
    //     if (!$request->session()->has('candidate_id')) {
    //         abort(401, 'Unauthorized access to the test.');
    //     }

    //     $testId = $request->session()->get('test_id');
    //     $test = Test::findOrFail($testId);

    //     // Display the test
    //     return view('tests.take', compact('test'));
    // }
}