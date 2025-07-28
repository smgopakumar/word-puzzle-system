<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Submission;
use App\Services\WordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class GameController extends Controller
{
    /**
     * The word validation service instance.
     *
     * @var WordService
     */
    protected WordService $wordService;

    /**
     * Inject WordService dependency.
     *
     * @param WordService $wordService
     */
    public function __construct(WordService $wordService)
    {
        $this->wordService = $wordService;
    }

    /**
     * Start a new game with a random puzzle.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
        ]);

        try {
            $puzzle = Str::random(14);

            $game = Game::create([
                'student_name' => $request->student_name,
                'puzzle_string' => $puzzle,
                'used_letters' => [],
            ]);

            return response()->json($game, 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to start game.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a word for the game.
     *
     * @param Request $request
     * @param Game $game
     * @return JsonResponse
     */
    public function submitWord(Request $request, Game $game): JsonResponse
    {
        $request->validate([
            'word' => 'required|string|min:2|max:14',
        ]);

        try {
            $word = strtolower($request->word);

            // Validate the word using the service
            if (!$this->wordService->isValidWord($word)) {
                return response()->json(['error' => 'Invalid English word.'], 400);
            }

            if (!$this->wordService->canUseWord($word, $game)) {
                return response()->json(['error' => 'Letters not available or reused.'], 400);
            }

            $points = strlen($word);

            // Record submission
            Submission::create([
                'game_id' => $game->id,
                'word' => $word,
                'score' => $points,
            ]);

            // Update game score and used letters
            $game->score += $points;
            $game->used_letters = $this->wordService->updateUsedLetters($word, $game);
            $game->save();

            return response()->json([
                'message' => 'Word accepted',
                'score' => $game->score,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Submission failed.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finish the game and return possible remaining words.
     *
     * @param Game $game
     * @return JsonResponse
     */
    public function finish(Game $game): JsonResponse
    {
        try {
            $game->is_completed = true;
            $game->save();

            $remaining = $this->wordService->getRemainingLetters($game);
            $possibleWords = $this->wordService->getPossibleWords($remaining);

            return response()->json([
                'final_score' => $game->score,
                'possible_remaining_words' => $possibleWords,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to finish game.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
