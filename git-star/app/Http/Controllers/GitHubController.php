<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class GitHubController extends Controller
{
    // Initiate the OAuth flow
    public function redirectToGitHub()
    {
        $clientId = env('GITHUB_CLIENT_ID');
        $redirectUri = env('GITHUB_REDIRECT_URI'); // e.g., http://127.0.0.1:8000/api/github/callback
        $scope = 'repo';  // This grants access to the repositories

        // Redirecting the user to GitHub OAuth page
        $url = "https://github.com/login/oauth/authorize?client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scope}";

        return response()->json(['url' => $url]);
    }

    // Handle the callback and get the access token
    public function handleGitHubCallback(Request $request)
    {

        $code = $request->get('code');

        if (!$code) {
            return response()->json(['error' => 'No code received from GitHub'], 400);
        }


        // GitHub OAuth token exchange
        $clientId = env('GITHUB_CLIENT_ID');
        $clientSecret = env('GITHUB_CLIENT_SECRET');
        $redirectUri = env('GITHUB_REDIRECT_URI');

//        return [ 'client_id' => $clientId,
//            'client_secret' => $clientSecret,
//            'code' => $code,
//            'redirect_uri' => $redirectUri];

        // Make the request to GitHub to exchange the code for an access token
        $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        // Check if the response contains the access token
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to get access token'], 500);
        }


        parse_str($response, $data);


        $accessToken = isset($data['access_token']) ? $data['access_token'] : null;
        return $accessToken;
        if (!isset($data['access_token'])) {
            return response()->json(['error' => 'No access token received'], 400);
        }

        // Store the access token for further API requests
        $accessToken = $data['access_token'];

        // You can store the token in the user's session or database
        // For simplicity, we just return the token here
        return response()->json(['access_token' => $accessToken]);
    }

    // Star a GitHub repository
    public function starRepository(Request $request)
    {
        $accessToken = $request->bearerToken();  // Assume the token is passed as a Bearer token

        if (!$accessToken) {
            return response()->json(['error' => 'No access token provided'], 400);
        }

        // Get the repository details (owner/repo)
        $repo = $request->input('repo', 'owner/repository');  // Example: 'username/repo'

        // Send the request to star the repository
        $response = Http::withToken($accessToken)
            ->put("https://api.github.com/user/starred/{$repo}");

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to star the repository'], 500);
        }

        return response()->json(['message' => 'Repository starred successfully']);
    }
}
