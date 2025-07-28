<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Log;

class WordService
{
    /**
     * Check if the given word is a valid English word.
     *
     * @param string $word
     * @return bool
     */
    public function isValidWord(string $word): bool
    {
        try {
            $dictionaryPath = storage_path('words.txt');
            if (!file_exists($dictionaryPath)) {
                Log::error('Dictionary file not found at: ' . $dictionaryPath);
                return false;
            }


            // Clean up each line and make it lowercase
            $dictionary = array_map('strtolower', array_map('trim', file($dictionaryPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));

            if (!in_array(strtolower($word), $dictionary)) {
                Log::info('Word not found: ' . strtolower($word));
                Log::info('Sample words from dictionary: ' . implode(', ', array_slice($dictionary, 0, 10)));
            }

            return in_array(strtolower($word), $dictionary);
        } catch (\Exception $e) {
            Log::error('Error checking valid word: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Check if the word can be formed from remaining letters of the puzzle.
     *
     * @param string $word
     * @param Game $game
     * @return bool
     */
    public function canUseWord(string $word, Game $game): bool
    {
        try {
            // Normalize puzzle string and split into characters
            $puzzle = str_split(strtolower($game->puzzle_string));
            $availableLetters = array_count_values($puzzle);

            // Log initial puzzle state
            Log::info("Puzzle string: {$game->puzzle_string}");

            // Count how many times each letter has already been used
            $usedLetters = array_count_values($game->used_letters ?? []);
            Log::info('Used letters: ' . implode('', $game->used_letters ?? []));

            // Subtract used letters from the available letters
            foreach ($usedLetters as $char => $count) {
                $availableLetters[$char] = max(0, ($availableLetters[$char] ?? 0) - $count);
            }

            // Prepare and log the word being checked
            $word = strtolower($word);
            $wordLetters = array_count_values(str_split($word));
            Log::info("Trying word: {$word}");
            Log::info('Available letters after usage: ' . json_encode($availableLetters));
            Log::info('Word letters: ' . json_encode($wordLetters));

            // Validate if the word can be formed with remaining letters
            foreach ($wordLetters as $char => $count) {
                if (!isset($availableLetters[$char]) || $availableLetters[$char] < $count) {
                    Log::info("Cannot form word '{$word}' — missing letter: '{$char}'");
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Error checking usable word: ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Update the used letters in the game after a word is submitted.
     *
     * @param string $word
     * @param Game $game
     * @return array
     */
    public function updateUsedLetters(string $word, Game $game): array
    {
        try {
            // Get the existing used letters
            $used = $game->used_letters ?? [];

            // Split the new word into letters and merge
            $updated = array_merge($used, str_split(strtolower($word)));

            return $updated;
        } catch (\Exception $e) {
            Log::error('Error updating used letters: ' . $e->getMessage());
            return $game->used_letters ?? [];
        }
    }


    /**
     * Get the remaining unused letters in the puzzle.
     *
     * @param Game $game
     * @return string
     */
    public function getRemainingLetters(Game $game): string
    {
        try {
            $puzzle = str_split(strtolower($game->puzzle_string));

            // Ensure $used is an array of letters
            $used = [];

            if (is_string($game->used_letters)) {
                $decoded = json_decode($game->used_letters, true);
                $used = is_array($decoded) ? $decoded : [];
            } elseif (is_array($game->used_letters)) {
                $used = $game->used_letters;
            }

            // Remove used letters one by one from puzzle array
            foreach ($used as $char) {
                $index = array_search($char, $puzzle);
                if ($index !== false) {
                    unset($puzzle[$index]);
                }
            }

            return implode('', $puzzle);
        } catch (\Exception $e) {
            Log::error('Error getting remaining letters: ' . $e->getMessage());
            return '';
        }
    }


    /**
     * Get all valid words that can be made from given letters.
     *
     * @param string $letters
     * @return array
     */
    public function getPossibleWords(string $letters): array
    {
        static $dictionary = null;
        $possibleWords = [];

        try {
            // Load and cache dictionary
            if (is_null($dictionary)) {
                $dictionaryPath = storage_path('words.txt');

                if (!file_exists($dictionaryPath)) {
                    Log::error('Dictionary file not found at: ' . $dictionaryPath);
                    return [];
                }

                $dictionary = array_map('strtolower', array_map('trim', file($dictionaryPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
            }

            $availableLetters = array_count_values(str_split(strtolower($letters)));

            foreach ($dictionary as $word) {
                $wordLetterCount = array_count_values(str_split($word));

                foreach ($wordLetterCount as $char => $count) {
                    if (!isset($availableLetters[$char]) || $availableLetters[$char] < $count) {
                        continue 2; // Skip this word
                    }
                }

                $possibleWords[] = $word;
            }

            return $possibleWords;
        } catch (\Exception $e) {
            Log::error('Error getting possible words: ' . $e->getMessage());
            return [];
        }
    }

    public function validateWord(string $word, Game $game): bool
    {
        // Step 1: Check if the word is valid according to dictionary
        // if (!$this->dictionaryService->isValidWord($word)) {
        //     return false;
        // }

        // Step 2: Get available letters from the puzzle
        $availableLetters = array_count_values(str_split(strtolower($game->puzzle_string)));

        // Step 3: Subtract the already used letters
        $usedLetters = is_array($game->used_letters)
            ? $game->used_letters
            : json_decode($game->used_letters, true) ?? [];

        foreach ($usedLetters as $used) {
            if (isset($availableLetters[$used])) {
                $availableLetters[$used]--;
                if ($availableLetters[$used] === 0) {
                    unset($availableLetters[$used]);
                }
            }
        }

        // Step 4: Check if the new word can be formed with remaining letters
        $neededLetters = array_count_values(str_split(strtolower($word)));

        foreach ($neededLetters as $char => $count) {
            if (!isset($availableLetters[$char]) || $availableLetters[$char] < $count) {
                return false;
            }
        }

        return true;
    }
}
