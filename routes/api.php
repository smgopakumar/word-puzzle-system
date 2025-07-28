<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LeaderboardController;

Route::post('/game/start', [GameController::class, 'start']);
Route::post('/game/{game}/submit', [GameController::class, 'submitWord']);
Route::post('/game/{game}/finish', [GameController::class, 'finish']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
