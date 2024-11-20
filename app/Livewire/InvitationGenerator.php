<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class InvitationGenerator extends Component
{
    public $invitationToken;
    public $invitationLink;

    public function generateLink()
    {
        $this->invitationToken = Str::random(32);
        
        // Generate route using token directly, not as part of URL
        $this->invitationLink = URL::route('invitation.show', ['token' => $this->invitationToken]);

        logger('Token generated: ' . $this->invitationToken);
        logger('Link generated: ' . $this->invitationLink);
    }

    public function render()
    {
        return view('livewire.invitation-generator', [
            'invitationToken' => $this->invitationToken,
            'invitationLink' => $this->invitationLink
        ]);
    }
}

