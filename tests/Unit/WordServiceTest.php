<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Services\WordService;
use App\Services\DictionaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class WordServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $wordService;
    protected $dictionaryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dictionaryService = Mockery::mock(DictionaryService::class);
        $this->app->instance(DictionaryService::class, $this->dictionaryService);

        $this->wordService = app(WordService::class);
    }

    /** @test */
    public function it_creates_a_game()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode([]),
        ]);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'puzzle_string' => 'apple',
        ]);
    }

    /** @test */
    public function test_it_returns_possible_words_from_letters()
    {
        $letters = 'lava';

        $expectedWords = ['lava', 'val', 'al'];

        // Assuming DictionaryService::isValidWord is mocked
        $this->dictionaryService
            ->shouldReceive('isValidWord')
            ->andReturnUsing(function ($word) {
                return in_array($word, ['lava', 'val', 'al']);
            });

        $result = $this->wordService->getPossibleWords($letters);

        sort($expectedWords);
        sort($result);

        $this->assertEquals($expectedWords, $result);
    }

    /** @test */
    public function it_validates_a_word_successfully()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode([]),
        ]);

        $this->dictionaryService
            ->shouldReceive('isValidWord')
            ->with('pal')
            ->andReturn(true);

        $isValid = $this->wordService->validateWord('pal', $game);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_rejects_invalid_word_not_in_dictionary()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode([]),
        ]);

        $this->dictionaryService
            ->shouldReceive('isValidWord')
            ->with('zzzz')
            ->andReturn(false);

        $isValid = $this->wordService->validateWord('zzzz', $game);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_rejects_word_with_insufficient_remaining_letters()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode(['a', 'p', 'p']),
        ]);

        $this->dictionaryService
            ->shouldReceive('isValidWord')
            ->with('apple')
            ->andReturn(true);

        $isValid = $this->wordService->validateWord('apple', $game);

        $this->assertFalse($isValid, 'Word should not be valid with already used letters');
    }

    /** @test */
    public function test_it_updates_used_letters_correctly()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode(['a']),
        ]);

        // Ensure used_letters is an array before merging
        $game->used_letters = json_decode($game->used_letters, true);

        $updated = $this->wordService->updateUsedLetters('ple', $game);

        $this->assertContains('p', $updated);
        $this->assertContains('l', $updated);
        $this->assertContains('e', $updated);
        $this->assertContains('a', $updated); // previously used letter should remain
        $this->assertCount(4, $updated); // a, p, l, e
    }

    /** @test */
    public function it_returns_correct_remaining_letters()
    {
        $game = Game::create([
            'student_name' => 'Test Student',
            'puzzle_string' => 'apple',
            'used_letters' => json_encode(['p', 'l']),
        ]);

        $remaining = $this->wordService->getRemainingLetters($game);
        $this->assertEqualsCanonicalizing(['a', 'p', 'e'], str_split($remaining));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
