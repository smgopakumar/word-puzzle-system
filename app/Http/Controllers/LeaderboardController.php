<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

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
            // Fetch top 10 unique words with their highest score
            $top = Submission::select('word', DB::raw('MAX(score) as score'))
                ->groupBy('word')
                ->orderByDesc('score')
                ->limit(10)
                ->get();

            // Return the top scores as JSON response
            return response()->json($top);
        } catch (\Exception $e) {
            // Log the exception if needed: logger($e->getMessage());

            // Return a generic error response
            return response()->json([
                'error' => 'Failed to fetch leaderboard',
                'message' => $e->getMessage() // For debugging; remove in production
            ], 500);
        }
    }
}
