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
        $invitationLink = Str::random(32);
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'invitation_link' => 'required|string|url',
        ]);

        $test = Test::create($validatedData);

        // Create test invitation
        TestInvitation::create([
            'test_id' => $test->id,
            'invitation_link' => $invitationLink,
            'email_list' => json_encode($validatedData['email_list']),
            'expires_at' => now()->addDays($validatedData['expiration_days']),
            'created_by' => auth()->id(),
        ]);

        // Here you would typically send emails to the email list
        // This is left as a TODO for you to implement based on your email sending setup

        return redirect()->route('tests.index')->with('success', 'Test created successfully. Invitation link: ' . route('invitation.show', ['invitationLink' => $invitationLink]));
    }

    public function show($id)
    {
        $test = Test::findOrFail($id);
        return view('tests.show', compact('test')); 
    }

    public function edit($id)
    {
        $test = Test::findOrFail($id); 
        return view('tests.edit', compact('test')); 
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $test = Test::findOrFail($id); 
        $test->update($request->only(['name', 'description'])); 

        return redirect()->route('tests.index')->with('success', 'Test updated successfully.'); 
    }

    public function destroy($id)
    {
        $test = Test::findOrFail($id); 
        $test->delete(); 

        return redirect()->route('tests.index')->with('success', 'Test deleted successfully.'); 
    }
}