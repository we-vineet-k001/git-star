<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GitHubController extends Controller
{
    public function redirectToGitHub()
    {
       
        return Socialite::driver('github')->redirect();
    }

    public function handleGitHubCallback(Request $request)
    {
        try {
            $user = Socialite::driver('github')->user();
            $githubToken = $user->token();

            // Assuming the repo name is passed via query or input (example: 'owner/repository')
            $repo = $request->input('repo', 'owner/repository');

            $client = new Client();
            $response = $client->get("https://api.github.com/repos/{$repo}/stargazers", [
                'headers' => [
                    'Authorization' => "Bearer {$githubToken}",
                    'Accept' => 'application/vnd.github.v3.star+json',
                ]
            ]);

            $stargazers = json_decode($response->getBody(), true);

            $hasStarred = collect($stargazers)->contains(function ($stargazer) use ($user) {
                return $stargazer['login'] === $user->getNickname();
            });

            return response()->json([
                'has_starred' => $hasStarred,
                'repo' => $repo,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch data from GitHub'], 500);
        }
    }
}
