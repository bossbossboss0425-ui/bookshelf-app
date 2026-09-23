<?php

namespace tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍はジャンルと多対多のリレーションを持つ(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $book->genres()->attach($genre->id);

        $this->assertTrue($book->genres->contains($genre));
    }

    /** @test */
    public function 書籍は複数のレビューを持つ(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $this->assertTrue($book->reviews->contains($review));
    }

    /** @test */
    public function レビューの平均評価が正しく算出される(): void
    {
        $book = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user1->id, 'rating' => 4]);
        Review::factory()->create(['book_id' => $book->id, 'user_id' => $user2->id, 'rating' => 2]);

        $this->assertEquals(3.0, round($book->reviews()->avg('rating'), 1));
    }
}
