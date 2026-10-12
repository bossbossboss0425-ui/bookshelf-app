<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍タイトルで検索できる(): void
    {
        Book::factory()->create(['title' => 'Laravel入門']);
        Book::factory()->create(['title' => 'PHP入門']);

        $response = $this->get('/books?keyword=Laravel');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->total() === 1
                && $books->first()->title === 'Laravel入門';
        });
    }

    /** @test */
    public function 著者名でも検索できる(): void
    {
        Book::factory()->create([
            'title' => 'プログラミング入門',
            'author' => '山田太郎',
        ]);

        Book::factory()->create([
            'title' => 'データベース入門',
            'author' => '佐藤花子',
        ]);

        $response = $this->get('/books?keyword=山田太郎');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->total() === 1
                && $books->first()->author === '山田太郎';
        });
    }

    /** @test */
    public function ジャンルで書籍を絞り込める(): void
    {
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);
        $targetBook = Book::factory()->create([
            'title' => '対象の書籍',
        ]);

        $otherBook = Book::factory()->create([
            'title' => '対象外の書籍',
        ]);

        $targetBook->genres()->attach($genre->id);

        $response = $this->get('/books?genre='.$genre->id);

        $response->assertOk();
        $response->assertViewHas('books', function ($books) use ($targetBook, $otherBook) {
            $ids = $books->pluck('id')->all();

            return in_array($targetBook->id, $ids)
                && ! in_array($otherBook->id, $ids);
        });
    }

    /** @test */
    public function 検索条件がページネーションのリンクに保持される(): void
    {
        Book::factory()->count(12)->create([
            'title' => 'Laravel入門',
        ]);

        $response = $this->get('/books?keyword=Laravel&sort=title');

        $response->assertOk();
        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 12
                && str_contains($books->nextPageUrl() ?? '', 'keyword=Laravel')
                && str_contains($books->nextPageUrl() ?? '', 'sort=title');
        });
    }
}
