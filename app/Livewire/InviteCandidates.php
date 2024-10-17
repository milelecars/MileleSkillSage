<?php

namespace App\Livewire;

use App\Models\TestInvitation;
use Livewire\Component;
use Illuminate\Support\Facades\Validator;

class InviteCandidates extends Component
{
    public string $newEmail = '';
    public array $emailList = [];
    public $testId;

    public function mount($testId)
    {
        $this->testId = $testId;
        // Load emails from session if they exist
        $this->emailList = session("test_{$testId}_emails", []);
    }

    public function addEmail()
    {
        $validated = Validator::make(
            ['email' => $this->newEmail],
            ['email' => 'required|email']
        )->validate();

        $this->emailList[] = $validated['email'];
        $this->newEmail = ''; // Clear the input

        // Store in session
        session(["test_{$this->testId}_emails" => $this->emailList]);
    }

    public function removeEmail($index)
    {
        unset($this->emailList[$index]);
        $this->emailList = array_values($this->emailList); // Reindex array
        
        // Update session
        session(["test_{$this->testId}_emails" => $this->emailList]);
    }

    public function submitInvitations()
    {
        if (empty($this->emailList)) {
            $this->addError('submission', 'Please add at least one email address.');
            return;
        }

        try {
            // Find the invitation record
            $invitation = TestInvitation::where('test_id', $this->testId)->firstOrFail();
            
            // Update the email list
            $invitation->update([
                'email_list' => $this->emailList
            ]);

            // Clear session
            session()->forget("test_{$this->testId}_emails");
            
            // Clear local array
            $this->emailList = [];

            session()->flash('message', 'Invitations have been sent successfully!');
            
            // Redirect to tests index or wherever appropriate
            return redirect()->route('tests.index');
            
        } catch (\Exception $e) {
            $this->addError('submission', 'Failed to send invitations. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.invite-candidates');
    }
}