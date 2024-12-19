<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class RejectionEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $candidate;

    public function __construct($candidate)
    {
        $this->candidate = $candidate;
        // Debug log the construction
        Log::debug('RejectionEmail constructed', [
            'candidate_email' => $candidate->email
        ]);
    }

    public function build()
    {
        // Debug log the configuration
        Log::debug('Building rejection email', [
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
            ->view('emails.candidate-rejection-template');
    }

    public function failed(\Exception $exception)
    {
        Log::error('Rejection email failed to send', [
            'candidate_email' => $this->candidate->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}