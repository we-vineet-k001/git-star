<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GitHubController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->middleware('api')->group(function () {
    Route::get('github/redirect', [GitHubController::class, 'redirectToGitHub']);
    Route::get('github/callback', [GitHubController::class, 'handleGitHubCallback']);
});
