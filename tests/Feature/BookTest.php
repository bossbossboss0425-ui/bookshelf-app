<?php

namespace tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストも書籍一覧および詳細画面を閲覧できる(): void
    {
        $book = Book::factory()->create();

        $this->get('/books')->assertStatus(200);
        $this->get("/books/{$book->id}")->assertStatus(200);
    }

    /** @test */
    public function 未認証ユーザーが書籍登録画面へ移動しようとするとログインへリダイレクトされる(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function 認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $bookData = [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'description' => '概要説明',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)->post('/books', $bookData);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', ['isbn' => '9784123456789']);
        $this->assertDatabaseHas('book_genre', ['genre_id' => $genre->id]);
    }

    /** @test */
    public function 作成者本人のみ書籍を更新できる(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::create(['name' => 'テストジャンル']);

        $updateData = [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => '2026-01-01',
            'description' => '更新後の説明文',
            'genres' => [$genre->id],
        ];

        // 他人が更新しようとすると403 Forbidden
        $this->actingAs($otherUser)->put("/books/{$book->id}", $updateData)
            ->assertStatus(403);

        // 本人は更新可能
        $this->actingAs($owner)->put("/books/{$book->id}", $updateData)
            ->assertRedirect("/books/{$book->id}");

        $this->assertDatabaseHas('books', ['title' => '更新後のタイトル']);
    }

    /** @test */
    public function 書籍削除時に関連データも適切に処理される(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($owner)->delete("/books/{$book->id}");

        $response->assertRedirect('/books');
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('book_genre', ['book_id' => $book->id]);
    }
}
