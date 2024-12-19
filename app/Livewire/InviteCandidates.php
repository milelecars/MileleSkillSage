<?php

namespace App\Livewire;

use App\Models\Test;
use Livewire\Component;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InviteCandidates extends Component
{
    public string $newEmail = '';
    public array $emailList = [];
    public $testId;
    
    protected $validationAttributes = ['newEmail' => 'email'];

    public function mount($testId)
    {
        $this->testId = $testId;
        $this->emailList = session("test_{$testId}_emails", []);
    }

    public function addEmail()
    {
        $this->validateOnly('newEmail', [
            'newEmail' => 'required|email'
        ]);

        $email = $this->newEmail;
        
        $existingInvitation = Invitation::where('test_id', $this->testId)
            ->whereJsonContains('invited_emails', $email)
            ->exists();

        if ($existingInvitation) {
            $this->addError('newEmail', 'This email has already been invited.');
            return;
        }
        
        if (in_array($email, $this->emailList)) {
            $this->addError('newEmail', 'This email has already been added to the current list.');
            return;
        }
        
        $this->emailList[] = $email;
        $this->newEmail = '';
        
        session(["test_{$this->testId}_emails" => $this->emailList]);
        $this->dispatch('email-added', email: $email);
    }

    public function removeEmail($index)
    {
        unset($this->emailList[$index]);
        $this->emailList = array_values($this->emailList);
        session(["test_{$this->testId}_emails" => $this->emailList]);
    }

    public function submitInvitations()
    {
        try {
            DB::beginTransaction();
            
            $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
            $test = Test::findOrFail($this->testId);
            
            // Ensure invited_emails is an array
            $existingEmails = is_array($invitation->invited_emails) ? $invitation->invited_emails : [];
            
            if (empty($this->emailList)) {
                session()->flash('info', 'No emails to send invitations to.');
                return;
            }
    
            $failedEmails = [];
            
            foreach ($this->emailList as $email) {
                try {
                    $invitationEmail = new InvitationEmail($invitation->invitation_link, $test->title);
                    Mail::to($email)->send($invitationEmail);
                } catch (\Exception $e) {
                    Log::error("Failed to send invitation email to {$email}: " . $e->getMessage());
                    $failedEmails[] = $email;
                }
            }
    
            if (!empty($failedEmails)) {
                DB::rollBack();
                $failedEmailsList = implode(', ', $failedEmails);
                $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
                return;
            }
    
            // Only if all emails were sent successfully, update the database
            $allEmails = array_unique(array_merge($existingEmails, $this->emailList));
            $invitation->update([
                'invited_emails' => $allEmails
            ]);
    
            DB::commit();
            
            session()->forget("test_{$this->testId}_emails");
            $this->emailList = [];
    
            session()->flash('success', 'Invitations have been sent successfully!');
            $this->dispatch('invitations-sent');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process invitations: " . $e->getMessage());
            session()->forget('success');
            $this->addError('submission', 'Failed to process invitations. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.invite-candidates');
    }
}