<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍の登録者本人は編集画面を表示できる(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->get("/books/{$book->id}/edit")
            ->assertOk();
    }

    /** @test */
    public function 他人の書籍の編集画面にはアクセスできない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->get("/books/{$book->id}/edit")
            ->assertForbidden();
    }

    /** @test */
    public function 書籍の登録者本人は書籍を削除できる(): void
    {
        $owner = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner)
            ->delete("/books/{$book->id}")
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 他人の書籍は削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->delete("/books/{$book->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 未ログインでは書籍の編集画面にアクセスできない(): void
    {
        $book = Book::factory()->create();

        $this->get("/books/{$book->id}/edit")
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function 未ログインでは書籍を削除できない(): void
    {
        $book = Book::factory()->create();

        $this->delete("/books/{$book->id}")
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 他人の書籍は更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $genre = Genre::create(['name' => 'テストジャンル']);
        $book->genres()->attach($genre->id);

        $originalTitle = $book->title;

        $response = $this->actingAs($otherUser)
            ->put("/books/{$book->id}", [
                'title' => '不正なタイトル変更',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'description' => $book->description,
                'image_url' => $book->image_url,
                'genres' => $book->genres()->pluck('genres.id')->toArray(),
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $originalTitle,
        ]);
    }
}
