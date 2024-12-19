<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $invitationLink;
    public $testName;

    public function __construct($invitationLink, $testName)
    {
        $this->invitationLink = $invitationLink;
        $this->testName = $testName;
        
        Log::debug('InvitationEmail constructed', [
            'test_name' => $testName
        ]);
    }

    public function build()
    {
        Log::debug('Building invitation email', [
            'configured_from_address' => Config::get('mail.from.address'),
            'configured_from_name' => Config::get('mail.from.name'),
            'mailer' => Config::get('mail.default'),
            'smtp_settings' => Config::get('mail.mailers.smtp')
        ]);
        
        $this->mailer('smtp');
        
        return $this
            ->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->subject('Invitation to Take a Test for Milele Motors')
            ->view('emails.invitation-email-template');
    }

    public function failed(\Exception $exception)
    {
        Log::error('Invitation email failed to send', [
            'test_name' => $this->testName,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
    
    public function submitInvitations()
    {
        try {
            DB::beginTransaction();
            
            Log::debug('Starting invitation submission process', [
                'test_id' => $this->testId,
                'email_count' => count($this->emailList),
                'mail_config' => [
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name'),
                    'mailer' => config('mail.default'),
                    'smtp' => config('mail.mailers.smtp')
                ]
            ]);
            
            $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
            $test = Test::findOrFail($this->testId);
            
            // Ensure invited_emails is an array
            $existingEmails = is_array($invitation->invited_emails) ? $invitation->invited_emails : [];
            
            if (empty($this->emailList)) {
                Log::info('No emails to send invitations to.');
                session()->flash('info', 'No emails to send invitations to.');
                return;
            }
    
            $failedEmails = [];
            
            foreach ($this->emailList as $email) {
                try {
                    Log::debug('Attempting to send invitation email', ['email' => $email]);
                    $invitationEmail = new InvitationEmail($invitation->invitation_link, $test->title);
                    Mail::mailer('smtp')->to($email)->send($invitationEmail);  // Fixed to match acceptance/rejection pattern
                    Log::debug('Invitation email sent successfully', ['email' => $email]);
                } catch (\Exception $e) {
                    Log::error("Failed to send invitation email", [
                        'email' => $email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $failedEmails[] = $email;
                }
            }
    
            if (!empty($failedEmails)) {
                DB::rollBack();
                $failedEmailsList = implode(', ', $failedEmails);
                Log::error('Failed to send some invitation emails', ['failed_emails' => $failedEmails]);
                $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
                return;
            }
    
            // Only if all emails were sent successfully, update the database
            $allEmails = array_unique(array_merge($existingEmails, $this->emailList));
            $invitation->update([
                'invited_emails' => $allEmails
            ]);
    
            DB::commit();
            
            Log::debug('Invitation process completed successfully', [
                'total_emails_sent' => count($this->emailList)
            ]);
            
            session()->forget("test_{$this->testId}_emails");
            $this->emailList = [];
    
            session()->flash('success', 'Invitations have been sent successfully!');
            $this->dispatch('invitations-sent');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process invitations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->forget('success');
            $this->addError('submission', 'Failed to process invitations. Please try again.');
        }
    }
}
