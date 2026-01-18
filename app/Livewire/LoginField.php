<?php

namespace App\Livewire;

use App\Models\Admin;
use App\Http\Controllers\OAuthController;
use Google\Client;
use Google\Service\Gmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class LoginField extends Component
{
    public $email;
    public $otp;
    public $otpGenerated = false;
    public $errorMessage = '';
    public $successMessage = '';

    
    public function generateOtp()
    {
        $validator = Validator::make(['email' => $this->email], [
            'email' => 'required|email|exists:admins,email',
        ]);
    
        if ($validator->fails()) {
            $this->errorMessage = 'Invalid email or no account associated with this email.';
            $this->successMessage = '';
            return;
        }
    
        $admin = Admin::where('email', $this->email)->first();
        $otp = random_int(100000, 999999);
    
        // Store OTP and expiration in the database
        $admin->otp = $otp;
        $admin->otp_expires_at = now()->addMinutes(5);
        $admin->save();

        // Load the email template
        $template = file_get_contents(resource_path('views/emails/otp-email-template.blade.php'));

        // Replace placeholders in the template
        $htmlContent = str_replace(
            ['{{ $otp }}', '{{ $admin->name }}'], 
            [$otp, $admin->name],
            $template
        );

        // Send the email using Gmail API
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
            $service = new Gmail($client);

            $message = new \Google\Service\Gmail\Message();
            $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
            $rawMessage .= "To: <{$admin->email}>\r\n";
            $rawMessage .= "Subject: =?utf-8?B?" . base64_encode("Your OTP Code") . "?=\r\n";
            $rawMessage .= "MIME-Version: 1.0\r\n";
            $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
            $rawMessage .= $htmlContent;
        
            $message->setRaw(base64_encode($rawMessage));
            $response = $service->users_messages->send('me', $message);
            Log::info('Gmail API response', ['response' => $response]);

            Log::info('OTP sent successfully', [
                'email' => $admin->email,
                'otp' => $otp,
            ]);

            $this->otpGenerated = true;
            $this->successMessage = 'OTP has been sent to your email.';
            $this->errorMessage = '';
        } catch (\Exception $e) {
            Log::error('Failed to send OTP', [
                'error' => $e->getMessage(),
                'email' => $this->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if it's an authentication error
            if (str_contains($e->getMessage(), 'Authentication required') || 
                str_contains($e->getMessage(), 'Authentication')) {
                $this->errorMessage = 'Gmail authentication is required. Please visit /admin/gmail/auth to authenticate.';
            } else {
                $this->errorMessage = 'Failed to send OTP. Please try again later.';
            }
            $this->successMessage = '';
        }
    }

    public function render()
    {
        return view('livewire.login-field');
    }
}
