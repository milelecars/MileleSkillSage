<?php

namespace App\Livewire;

use App\Models\Test;
use Livewire\Component;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Google\Client;
use Google\Service\Gmail;

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

        // Ensure OAuth token is available
        $this->checkAccessToken();
    }

    private function checkAccessToken()
    {
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
        } catch (\Exception $e) {
            Log::info('Access token check failed. Redirecting to Google login.');
            session()->flash('info', 'Please authenticate with Google to send emails.');
            return redirect()->route('google.login', ['testId' => $this->testId]);
        }
    }

    public function addEmail()
    {
        $this->validateOnly('newEmail', [
            'newEmail' => 'required|email',
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
    }

    public function removeEmail($index)
    {
        unset($this->emailList[$index]);
        $this->emailList = array_values($this->emailList);
        session(["test_{$this->testId}_emails" => $this->emailList]);
    }

    
    // public function submitInvitations()
    // {
    //     $accessToken = session('access_token');
    //     if (!$accessToken) {
    //         return redirect()->route('google.login', ['testId' => $this->testId]);
    //     }
    
    //     $client = new Client();
    //     $client->setAccessToken($accessToken);
    
    //     if ($client->isAccessTokenExpired()) {
    //         $oauthController = new OAuthController();
    //         $newToken = $oauthController->refreshToken();
    //         if (!$newToken) {
    //             return redirect()->route('google.login', ['testId' => $this->testId]);
    //         }
    //         $client->setAccessToken($newToken);
    //     }
    
    //     // Step 3: Create Gmail API Service
    //     $service = new Gmail($client);
    
    //     $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
    //     $test = Test::findOrFail($this->testId);
    
    //     $failedEmails = [];
    
    //     foreach ($this->emailList as $email) {
    //         try {
    //             Log::info('Attempting to send email', [
    //                 'to' => $email,
    //                 'token_status' => $client->getAccessToken(),
    //                 'token_expiry' => session('expires_in')
    //             ]);
    //             // Step 4: Build the Email
    //             $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
    //             $rawMessage .= "To: <$email>\r\n";
    //             $rawMessage .= "Subject: Invitation to Take a Test for Milele Motors\r\n\r\n";
    //             $rawMessage .= "Hello, you have been invited to take a test. Click here: {$invitation->invitation_link}";
    
    //             $encodedMessage = base64_encode($rawMessage);
    //             $encodedMessage = str_replace(['+', '/', '='], ['-', '_', ''], $encodedMessage); // Make it URL-safe
    
    //             $message = new Gmail\Message();
    //             $message->setRaw($encodedMessage);
    
    //             // Step 5: Send the Email
    //             $service->users_messages->send('me', $message);
    //         } catch (\Exception $e) {
    //             Log::error("Failed to send invitation email to {$email}: " . $e->getMessage());
    //             $failedEmails[] = $email;
    //         }
    //     }
    
    //     if (!empty($failedEmails)) {
    //         $failedEmailsList = implode(', ', $failedEmails);
    //         $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
    //     } else {
    //         session()->flash('success', 'Invitations have been sent successfully!');
    //     }
    // }

    // public function submitInvitations()
    // {
    //     try {
    //         DB::beginTransaction();
        
    //         try {
    //             $oAuthController = new OAuthController();
    //             $client = $oAuthController->getClient();
    //             Log::debug('Successfully got Google client');
    //         } catch (\Exception $e) {
    //             Log::debug('Authentication required, redirecting to Google login');
    //             return redirect()->route('google.login', ['testId' => $this->testId]);
    //         }
    
    //         $service = new Gmail($client);
    
    //         $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
    //         $test = Test::findOrFail($this->testId);
            
    //         $existingEmails = is_array($invitation->invited_emails) ? $invitation->invited_emails : [];
    //         $failedEmails = [];
    
    //         foreach ($this->emailList as $email) {
    //             try {
    //                 $message = new \Google\Service\Gmail\Message();
                    
    //                 $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
    //                 $rawMessage .= "To: <$email>\r\n";
    //                 $rawMessage .= 'Subject: =?utf-8?B?' . base64_encode("Invitation to Take a Test for Milele Motors") . "?=\r\n";
    //                 $rawMessage .= "MIME-Version: 1.0\r\n";
    //                 $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    //                 $rawMessage .= "Hello, you have been invited to take a test. Click here: {$invitation->invitation_link}";
    
    //                 $message->setRaw(base64_encode($rawMessage));
                    
    //                 $service->users_messages->send('me', $message);
    //                 Log::info("Email sent successfully to {$email}");
    //             } catch (\Exception $e) {
    //                 Log::error("Failed to send to {$email}: " . $e->getMessage());
    //                 $failedEmails[] = $email;
    //             }
    //         }
    
    //         if (!empty($failedEmails)) {
    //             DB::rollBack();
    //             $failedEmailsList = implode(', ', $failedEmails);
    //             $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
    //             return;
    //         }
    
    //         $allEmails = array_unique(array_merge($existingEmails, $this->emailList));
    //         $invitation->update(['invited_emails' => $allEmails]);
    
    //         DB::commit();
            
    //         session()->flash('success', 'Invitations sent successfully!');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Failed to process invitations: " . $e->getMessage());
    //         $this->addError('email_error', "Failed to process invitations");
    //     }
    // }
    
    public function submitInvitations()
    {
        try {
            DB::beginTransaction();
            
            $oAuthController = new OAuthController();
            try {
                $client = $oAuthController->getClient();
            } catch (\Exception $e) {
                return redirect()->route('google.login', ['testId' => $this->testId]);
            }

            $service = new Gmail($client);
            
            $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
            $test = Test::findOrFail($this->testId);
            
            $existingEmails = is_array($invitation->invited_emails) ? $invitation->invited_emails : [];
            $failedEmails = [];

            foreach ($this->emailList as $email) {
                try {
                    $message = new \Google\Service\Gmail\Message();
                    
                    $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
                    $rawMessage .= "To: <$email>\r\n";
                    $rawMessage .= 'Subject: =?utf-8?B?' . base64_encode("Invitation to Take a Test for Milele Motors") . "?=\r\n";
                    $rawMessage .= "MIME-Version: 1.0\r\n";
                    $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
                    $rawMessage .= "Hello, you have been invited to take a test. Click here: {$invitation->invitation_link}";

                    $message->setRaw(base64_encode($rawMessage));
                    
                    $service->users_messages->send('me', $message);
                    Log::info("Email sent successfully to {$email}");
                } catch (\Exception $e) {
                    Log::error("Failed to send to {$email}: " . $e->getMessage());
                    $failedEmails[] = $email;
                }
            }

            if (!empty($failedEmails)) {
                DB::rollBack();
                $failedEmailsList = implode(', ', $failedEmails);
                $this->addError('email_error', "Failed to send emails to: {$failedEmailsList}");
                return;
            }

            $allEmails = array_unique(array_merge($existingEmails, $this->emailList));
            $invitation->update(['invited_emails' => $allEmails]);

            DB::commit();
            
            session()->flash('success', 'Invitations sent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process invitations: " . $e->getMessage());
            $this->addError('email_error', "Failed to process invitations");
        }
    }
    
    public function render()
    {
        return view('livewire.invite-candidates');
    }
}
