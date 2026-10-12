<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューがない場合は空の読書レポートを表示する(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && $stats['summary']['average_rating'] === 0;
        });
    }

    /** @test */
    public function レビューがある場合は件数と平均評価を正しく集計する(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        $this->assertSame(2, $stats['summary']['total_reviews']);
        $this->assertSame(2, $stats['summary']['books_read']);
        $this->assertEquals(4.0, $stats['summary']['average_rating']);

    }

    /** @test */
    public function 評価分布と高評価書籍とジャンル別評価を集計する(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'ミステリー',
        ]);

        $book1 = Book::factory()->create([
            'title' => '高評価の本',
            'author' => '著者A',
        ]);
        $book1->genres()->attach($genre->id);

        $book2 = Book::factory()->create([
            'title' => '普通の本',
            'author' => '著者B',
        ]);
        $book2->genres()->attach($genre->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);

        $stats = $response->viewData('stats');

        $this->assertSame([0, 0, 1, 0, 1], $stats['rating_distribution']->all());

        $this->assertCount(1, $stats['top_rated_books']);
        $this->assertSame('高評価の本', $stats['top_rated_books'][0]['title']);
        $this->assertSame(5, $stats['top_rated_books'][0]['rating']);

        $this->assertCount(1, $stats['genre_ratings']);
        $this->assertSame('ミステリー', $stats['genre_ratings'][0]['name']);
        $this->assertSame(2, $stats['genre_ratings'][0]['count']);
        $this->assertEquals(4.0, $stats['genre_ratings'][0]['average_rating']);
    }
}
