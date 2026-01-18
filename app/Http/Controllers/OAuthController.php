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

        $tokenPath = storage_path('app/token.json');
        $accessToken = null;
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {
            Log::debug('Token is expired or missing');
            
            $refreshToken = $client->getRefreshToken();
            if ($refreshToken) {
                Log::debug('Refreshing token');
                $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                
                // Preserve the refresh token if a new one wasn't provided
                if (!isset($newToken['refresh_token']) && $accessToken && isset($accessToken['refresh_token'])) {
                    $newToken['refresh_token'] = $accessToken['refresh_token'];
                }
                
                // Save the refreshed token
                file_put_contents($tokenPath, json_encode($newToken));
                Log::debug('Token refreshed successfully');
                
            } else {
                Log::error('No refresh token available - deleting invalid token file');
                // Delete the invalid token file so user is forced to re-authenticate
                if (file_exists($tokenPath)) {
                    unlink($tokenPath);
                }
                throw new \Exception('Authentication required');
            }
        }

        return $client;
    }

    public function redirectToGoogle($testId = null)
    {
        if ($testId) {
            session()->put('test_id', $testId); 
            Log::debug('Storing test_id in session', ['test_id' => $testId]);
        } else {
            session()->put('admin_gmail_auth', true);
            Log::debug('Admin Gmail authentication initiated');
        }

        $client = new Client();
        $client->setApplicationName('Milele SkillSage');
        $client->setScopes(Gmail::MAIL_GOOGLE_COM);
        $client->setAuthConfig(storage_path('app/client_secret.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        
        // Set redirect URI - must match Google Cloud Console configuration
        $redirectUri = url('/oauth2/callback');
        $client->setRedirectUri($redirectUri);
        Log::debug('Setting redirect URI', ['redirect_uri' => $redirectUri]);

        $authUrl = $client->createAuthUrl();
        Log::debug('Redirecting to Google OAuth URL', ['url' => $authUrl]);
        return redirect($authUrl);
    }

    public function redirectToGoogleForAdmin()
    {
        return $this->redirectToGoogle(null);
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
            
            // Set redirect URI - must match Google Cloud Console configuration
            $redirectUri = url('/oauth2/callback');
            $client->setRedirectUri($redirectUri);

            // Exchange authorization code for access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($request->code);
            $client->setAccessToken($accessToken);

            if (array_key_exists('error', $accessToken)) {
                throw new \Exception(join(', ', $accessToken));
            }
            
            // IMPORTANT: Verify refresh token exists
            if (!isset($accessToken['refresh_token'])) {
                Log::warning('No refresh token received. User may need to revoke and re-authorize.');
                // Still save the token, but log the issue
            }
            
            // Save the token
            $tokenPath = storage_path('app/token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            chmod($tokenPath, 0600);

            Log::debug('Saving token to file', [
                'path' => $tokenPath,
                'token_data' => array_keys($accessToken),
                'has_refresh_token' => isset($accessToken['refresh_token'])
            ]);

            // Check if this is admin Gmail authentication (not test invitation)
            if (session('admin_gmail_auth')) {
                session()->forget('admin_gmail_auth');
                Log::debug('Admin Gmail authentication completed successfully');
                return redirect()->route('login')
                    ->with('success', 'Gmail authentication successful! You can now generate OTP.');
            }

            $testId = session('test_id');
            if (!$testId) {
                Log::error('Test ID not found in session');
                throw new \Exception('Test ID not found in session');
            }

            Log::debug('OAuth flow completed successfully', ['test_id' => $testId]);
            return redirect()->to("/tests/{$testId}/invite");

        } catch (\Exception $e) {
            Log::error('OAuth callback error: ' . $e->getMessage());
            
            // Check if this is admin Gmail authentication
            if (session('admin_gmail_auth')) {
                session()->forget('admin_gmail_auth');
                return redirect()->route('login')
                    ->with('error', 'Gmail authentication failed. Please try again.');
            }
            
            $testId = session('test_id') ?? 'default';
            return redirect()->route('google.login', ['testId' => $testId])
                           ->with('error', 'Authentication failed. Please try again.');
        }
    }
}