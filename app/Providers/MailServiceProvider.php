<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use App\Mail\Transport\GmailTransport;
use Google_Client;

class MailServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bind the GmailTransport to the MailManager
        $this->app->extend('mail.manager', function (MailManager $manager) {
            $manager->extend('gmail', function () {
                $client = new Google_Client();
                $client->setClientId(env('GOOGLE_CLIENT_ID'));
                $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
                $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
                $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));
                $client->addScope('https://www.googleapis.com/auth/gmail.send');

                return new GmailTransport($client);
            });

            return $manager;
        });
    }

    public function boot()
    {
        //
    }
}
