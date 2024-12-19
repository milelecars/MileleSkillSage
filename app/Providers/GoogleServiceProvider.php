// <?php

// namespace App\Providers;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\ServiceProvider;
// use Google_Client;
// use App\Mail\Transport;
// use Google_Service_Gmail;
// use Symfony\Component\Mailer\Transport\TransportInterface;

// class GoogleServiceProvider extends ServiceProvider
// {
// public function register()
//     {
//         // No need to register anything in the container here
//     }

//     public function boot()
//     {
//         Mail::extend('gmail', function () {
//             $client = new Google_Client();
//             $client->setClientId(env('GOOGLE_CLIENT_ID'));
//             $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
//             $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
//             $client->setAccessType('offline');
//             $client->setPrompt('consent');
//             $client->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
//             $client->refreshToken(env('GOOGLE_REFRESH_TOKEN'));

//             return new GmailTransport($client);
//         });
//     }
// }
