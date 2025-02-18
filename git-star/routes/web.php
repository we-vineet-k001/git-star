<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GitHubController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->middleware('api')->group(function () {
    Route::get('/login/github', [GitHubController::class, 'redirectToGitHub']);
    Route::get('github/callback', [GitHubController::class, 'handleGitHubCallback']);
    Route::post('star-repository', [GitHubController::class, 'starRepository']);  // Add route to star the repo
});
