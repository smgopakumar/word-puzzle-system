<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Game;

class LeaderboardController extends Controller
{
    /**
     * Display the top 10 high scores from submissions.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $result = Game::with('submissions:game_id,word,score')
                ->select('id', 'student_name', 'score') // score here is total score of the game
                ->get()
                ->map(function ($game) {
                    return [
                        'student_name' => $game->student_name,
                        'max_score' => $game->score,
                        'top_words' => $game->submissions
                    ];
                });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch leaderboard',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
