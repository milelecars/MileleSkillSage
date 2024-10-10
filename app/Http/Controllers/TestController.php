<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test; // Make sure to import the Test model
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
        if ($request->has('generate_link')) {
            $generatedLink = $this->generateInvitationLink();
            
            // Store all the old input, and add the generated link
            $oldInput = $request->except('_token', 'generate_link');
            $oldInput['invitation_link'] = $generatedLink;
            
            return redirect()->back()->withInput($oldInput);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'invitation_link' => 'required|string|url',
        ]);

        $test = Test::create($validatedData);

        return redirect()->route('tests.index')->with('success', 'Test created successfully.');
    }

    private function generateInvitationLink()
    {
        $token = Str::random(32);
        return url("/test-invitation/{$token}");
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
