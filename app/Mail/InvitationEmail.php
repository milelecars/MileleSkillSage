<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitationLink;
    public $testName;

    public function __construct($invitationLink, $testName)
    {
        $this->invitationLink = $invitationLink;
        $this->testName = $testName;
    }

    public function build()
    {
        return $this->subject('Invitation to Take a Test for Milele Motors')
                    ->view('emails.invitation-email-template');
    }
}