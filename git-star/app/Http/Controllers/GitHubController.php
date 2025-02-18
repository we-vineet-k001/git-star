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
        $client_id = env('GITHUB_CLIENT_ID');
        $redirect_uri = env('GITHUB_REDIRECT_URI'); // e.g., http://127.0.0.1:8000/api/github/callback
        $scope = 'repo';
        // Redirecting the user to GitHub OAuth page
        $url = "https://github.com/login/oauth/authorize?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}";

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
        $client_id = env('GITHUB_CLIENT_ID');
        $client_secret = env('GITHUB_CLIENT_SECRET');
        $redirect_uri = env('GITHUB_REDIRECT_URI');


        // Make the request to GitHub to exchange the code for an access token
        $response = Http::asForm()->post('https://github.com/login/oauth/access_token', [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        ]);

        // Check if the response contains the access token
        if ($response->failed()) {
            return response()->json(['error' => 'Failed to get access token'], 500);
        }


        parse_str($response, $data);


        $accessToken = isset($data['access_token']) ? $data['access_token'] : null;

        if (!$accessToken) {
            return response()->json(['error' => 'No access token received'], 400);
        }

        $repo = 'we-shivam-g001/customer_product_project_using_vaahcms';
        $starResponse = Http::withToken($accessToken)
            ->put("https://api.github.com/user/starred/{$repo}", []);

        if ($starResponse->failed()) {
            return response()->json([
                'error' => 'Failed to star the repository',
                'details' => $starResponse->json()
            ], 500);
        }
        return response()->json([
            'message' => 'Repository starred successfully',
            'access_token' => $accessToken
        ]);

//        return response()->json(['access_token' => $accessToken]);
    }


}
