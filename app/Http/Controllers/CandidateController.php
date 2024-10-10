<?php

namespace App\Http\Controllers;

use App\Models\Test;
use Illuminate\Http\Request;
use App\Models\TestInvitation;

class CandidateController extends Controller
{
    public function dashboard(Request $request)
    {
        // if ($request->user()->role !== 'candidate') {
        //     abort(403, 'Unauthorized action.');
        // }
    
        // $invitationLink = session('invitation_link');
        // $candidateEmail = session('candidate_email');
        
        // if (!$invitationLink || !$candidateEmail) {
        //     return redirect()->route('invitation.expired');
        // }

        // $invitation = TestInvitation::where('invitation_link', $invitationLink)
        //     ->where('expires_at', '>', now())
        //     ->first();

        // if (!$invitation) {
        //     return redirect()->route('invitation.expired');
        // }

        // return view('candidate.dashboard', [
        //     'invitation' => $invitation,
        //     'candidateEmail' => $candidateEmail
        // ]);
        
        
        // Fetch the specific test with the name 'AGCT test'
        $test = Test::where('name', 'AGCT Test')->first(); 

        if (!$test) {
            return redirect()->back()->with('error', 'Test not found.');
        }

        return view('candidate.dashboard', compact('test'));

    }

    public function startTest()
    {
        $invitationLink = session('invitation_link');
        $candidateEmail = session('candidate_email');
        
        if (!$invitationLink || !$candidateEmail) {
            return redirect()->route('invitation.expired');
        }

        $invitation = TestInvitation::where('invitation_link', $invitationLink)
            ->where('expires_at', '>', now())
            ->first();

        if (!$invitation) {
            return redirect()->route('invitation.expired');
        }

        // Logic to start the test
        return view('candidate.test', [
            'test' => $invitation->test,
            'candidateEmail' => $candidateEmail
        ]);
    }
}