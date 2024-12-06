<?php

namespace App\Livewire;

use App\Models\Test;
use Livewire\Component;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


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
            $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
            $existingEmails = $invitation->invited_emails ?? [];
            $newEmails = array_diff($this->emailList, $existingEmails);
            
            if (empty($newEmails)) {
                session()->flash('info', 'No new emails to send invitations to.');
                return;
            }
    
            // Try to send emails first
            $failedEmails = $this->sendInvitationEmails($invitation);
            
            if (!empty($failedEmails)) {
                // If any emails failed, don't update the database
                $failedEmailsList = implode(', ', $failedEmails);
                $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
                return;
            }
    
            // Only if all emails were sent successfully, update the database
            $allEmails = array_unique(array_merge($existingEmails, $this->emailList));
            $invitation->update([
                'invited_emails' => $allEmails
            ]);
    
            // Clear session and reset state
            session()->forget("test_{$this->testId}_emails");
            $this->emailList = [];
    
            session()->flash('success', 'Invitations have been sent successfully!');
            $this->dispatch('invitations-sent');
    
        } catch (\Exception $e) {
            \Log::error("Failed to process invitations: " . $e->getMessage());
            session()->forget('success');
            $this->addError('submission', 'Failed to process invitations. Please try again.');
        }
    }
    
    private function sendInvitationEmails(Invitation $invitation)
    {
        $test = Test::findOrFail($this->testId);
        $failedEmails = [];
        
        $mail = new PHPMailer(true);
    
        try {
            // Configure PHPMailer
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = config('mail.mailers.smtp.port');
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->isHTML(true);
            $mail->Subject = 'Invitation to Take a Test for Milele Motors';
    
            // Prepare email template
            $emailTemplate = view('emails.invitation-email-template', [
                'invitationLink' => $invitation->invitation_link,
                'testName' => $test->title
            ])->render();
    
            $mail->Body = $emailTemplate;
    
            // Try to send to each email individually
            foreach ($this->emailList as $email) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($email);
                    
                    if (!$mail->send()) {
                        \Log::error("Failed to send invitation email to {$email}: " . $mail->ErrorInfo);
                        $failedEmails[] = $email;
                    }
                } catch (\Exception $e) {
                    \Log::error("Error sending to {$email}: " . $e->getMessage());
                    $failedEmails[] = $email;
                }
            }
    
        } catch (\Exception $e) {
            \Log::error("SMTP Configuration Error: " . $e->getMessage());
            // If SMTP configuration fails, consider all emails as failed
            $failedEmails = $this->emailList;
        }
    
        return $failedEmails;
    }

    public function render()
    {
        return view('livewire.invite-candidates');
    }
}