<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSortTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍を登録日の新しい順に並べられる(): void
    {
        $oldBook = Book::factory()->create([
            'title' => '古い書籍',
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'title' => '新しい書籍',
            'created_at' => now(),
        ]);

        $response = $this->get('/books?sort=latest');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($oldBook, $newBook) {
            return $books->first()->id === $newBook->id
                && $books->last()->id === $oldBook->id;
        });
    }

    /** @test */
    public function 書籍を登録日の古い順に並べられる(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get('/books?sort=oldest');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($oldBook, $newBook) {
            return $books->first()->id === $oldBook->id
                && $books->last()->id === $newBook->id;
        });
    }

    /** @test */
    public function 書籍をタイトルの昇順に並べられる(): void
    {
        $bookB = Book::factory()->create(['title' => 'Bの書籍']);
        $bookA = Book::factory()->create(['title' => 'Aの書籍']);

        $response = $this->get('/books?sort=title');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($bookA, $bookB) {
            return $books->first()->id === $bookA->id
                && $books->last()->id === $bookB->id;
        });
    }

    /** @test */
    public function 評価の高い書籍を先に表示し未評価の書籍を最後に表示する(): void
    {
        $lowRatedBook = Book::factory()->create();
        $highRatedBook = Book::factory()->create();
        $unratedBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 2,
        ]);

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        $response = $this->get('/books?sort=rating');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($highRatedBook, $lowRatedBook, $unratedBook) {
            $ids = $books->pluck('id')->all();

            return array_search($highRatedBook->id, $ids, true)
                < array_search($lowRatedBook->id, $ids, true)
                && $books->last()->id === $unratedBook->id;
        });
    }
}
