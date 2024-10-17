<?php

namespace App\Livewire;

use App\Models\Test;
use Livewire\Component;
use App\Mail\InvitationEmail;
use App\Models\TestInvitation;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class InviteCandidates extends Component
{
    public string $newEmail = '';
    public array $emailList = [];
    public $testId;

    // Add this line to enable real-time validation
    protected $validationAttributes = ['newEmail' => 'email'];

    public function mount($testId)
    {
        $this->testId = $testId;
        // Load emails from session if they exist
        $this->emailList = session("test_{$testId}_emails", []);
    }

    public function addEmail()
    {
        $this->validateOnly('newEmail', [
            'newEmail' => 'required|email'
        ]);

        $email = $this->newEmail;

        // Check if the email is already in the database
        $existingInvitation = TestInvitation::where('test_id', $this->testId)
            ->whereJsonContains('email_list', $email)
            ->exists();

        if ($existingInvitation) {
            $this->addError('newEmail', 'This email has already been invited.');
            return;
        }

        // Check if the email is already in the current session list
        if (in_array($email, $this->emailList)) {
            $this->addError('newEmail', 'This email has already been added to the current list.');
            return;
        }

        // If email is not in DB and not in current list, add it
        $this->emailList[] = $email;
        $this->newEmail = ''; // Clear the input

        // Store in session
        session(["test_{$this->testId}_emails" => $this->emailList]);

        $this->dispatch('email-added', email: $email);
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
        try {
            // Find the invitation record
            $invitation = TestInvitation::where('test_id', $this->testId)->firstOrFail();
            
            // Send invitation emails
            $this->sendInvitationEmails($invitation);
            
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

    private function sendInvitationEmails(TestInvitation $invitation)
    {
        $test = Test::findOrFail($this->testId);
        
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = config('mail.mailers.smtp.port');

            // Sender
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Invitation to Take a Test for Milele Motors';

            // Load the email template
            $emailTemplate = view('emails.invitation-email-template', [
                'invitationLink' => $invitation->invitation_link,
                'testName' => $test->name
            ])->render();

            $mail->Body = $emailTemplate;

            foreach ($this->emailList as $email) {
                $mail->clearAddresses();
                $mail->addAddress($email);

                if (!$mail->send()) {
                    \Log::error("Failed to send invitation email to {$email}: " . $mail->ErrorInfo);
                }
            }
        } catch (Exception $e) {
            \Log::error("Error sending invitation emails: " . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.invite-candidates');
    }
}