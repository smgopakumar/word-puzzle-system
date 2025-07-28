<?php

namespace App\Services;

class DictionaryService
{
    protected array $dictionary;

    public function __construct()
    {
        // Load a sample dictionary file or array
        $this->dictionary = file(storage_path('app/dictionary.txt'), FILE_IGNORE_NEW_LINES);
    }

    public function isValidWord(string $word): bool
    {
        return in_array(strtolower($word), $this->dictionary);
    }

    public function getWordsFromLetters(string $letters): array
    {
        $letterCounts = array_count_values(str_split($letters));
        $validWords = [];

        foreach ($this->dictionary as $word) {
            $wordCounts = array_count_values(str_split($word));

            $canForm = true;
            foreach ($wordCounts as $char => $count) {
                if (!isset($letterCounts[$char]) || $letterCounts[$char] < $count) {
                    $canForm = false;
                    break;
                }
            }

            if ($canForm) {
                $validWords[] = $word;
            }
        }

        return $validWords;
    }
}
