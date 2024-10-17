<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class InvitationGenerator extends Component
{
    public $invitationLink;

    public function generateLink()
    {
        $token = Str::random(32);
        $this->invitationLink = URL::route('invitation.show', ['invitationLink' => $token]);
        // $this->invitationLink = URL::secure('invitation/' . $token); 


        logger('Link generated: ' . $this->invitationLink);
    }

    public function render()
    {
        return view('livewire.invitation-generator');
    }
}
