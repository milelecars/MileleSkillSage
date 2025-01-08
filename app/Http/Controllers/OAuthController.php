<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Gmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    public function getClient()
    {
        $client = new Client();
        $client->setApplicationName('Milele SkillSage');
        $client->setScopes(Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(storage_path('app/client_secret.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Check for token
        $tokenPath = storage_path('app/token.json');
        if (file_exists($tokenPath)) {
            Log::debug('Found existing token file');
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            Log::debug('Token is expired or missing');
            if ($client->getRefreshToken()) {
                Log::debug('Attempting to refresh token');
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                Log::debug('No refresh token available');
                throw new \Exception('Authentication required');
            }
        }

        return $client;
    }

    public function redirectToGoogle($testId)
    {
        session()->put('test_id', $testId); 
        Log::debug('Storing test_id in session', ['test_id' => $testId]);

        $client = new Client();
        $client->setApplicationName('Milele SkillSage');
        $client->setScopes(Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(storage_path('app/client_secret.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $authUrl = $client->createAuthUrl();
        Log::debug('Redirecting to Google OAuth URL', ['url' => $authUrl]);
        return redirect($authUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            Log::debug('Handling OAuth callback', [
                'code' => $request->code,
                'stored_test_id' => session('test_id')  
            ]);
            
            $client = new Client();
            $client->setApplicationName('Milele SkillSage');
            $client->setScopes(Gmail::MAIL_GOOGLE_COM);
            $client->setAuthConfig(storage_path('app/client_secret.json'));
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            // Exchange authorization code for access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);
            $client->setAccessToken($accessToken);

            // Check for errors
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(join(', ', $accessToken));
            }

            // Save the token
            $tokenPath = storage_path('app/token.json');
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            Log::debug('Saving token to file', [
                'path' => $tokenPath,
                'token_data' => array_keys($accessToken)
            ]);

            file_put_contents($tokenPath, json_encode($accessToken));
            chmod($tokenPath, 0600);

            $testId = session('test_id');
            if (!$testId) {
                Log::error('Test ID not found in session');
                throw new \Exception('Test ID not found in session');
            }

            Log::debug('OAuth flow completed successfully', ['test_id' => $testId]);
            return redirect()->to("/tests/{$testId}/invite");

        } catch (\Exception $e) {
            Log::error('OAuth callback error: ' . $e->getMessage());
            $testId = session('test_id') ?? 'default';
            return redirect()->route('google.login', ['testId' => $testId])
                           ->with('error', 'Authentication failed. Please try again.');
        }
    }
}