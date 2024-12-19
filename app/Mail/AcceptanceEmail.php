<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AcceptanceEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $candidate;

    public function __construct($candidate)
    {
        $this->candidate = $candidate;
        // Debug log the construction
        Log::debug('AcceptanceEmail constructed', [
            'candidate_email' => $candidate->email
        ]);
    }

    public function build()
    {
        // Debug log the configuration
        Log::debug('Building acceptance email', [
            'configured_from_address' => Config::get('mail.from.address'),
            'configured_from_name' => Config::get('mail.from.name'),
            'mailer' => Config::get('mail.default'),
            'smtp_settings' => Config::get('mail.mailers.smtp')
        ]);
        
        // Ensure we're using SMTP configuration
        $this->mailer('smtp');
        
        return $this
            ->from(Config::get('mail.from.address'), Config::get('mail.from.name'))
            ->subject('Your Application Status - Milele Motors')
            ->view('emails.candidate-acceptance-template');
    }

    public function failed(\Exception $exception)
    {
        Log::error('Acceptance email failed to send', [
            'candidate_email' => $this->candidate->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}