<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍一覧がjson形式で取得できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'author', 'isbn', 'genres', 'average_rating', 'reviews_count'],
                ],
            ]);
    }

    /** @test */
    public function 指定idの書籍詳細が取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $book->id);
    }

    /** @test */
    public function 存在しないidの場合は404エラーレスポンスが返る(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => '指定された書籍が見つかりませんでした。',
            ]);
    }

    /** @test */
    public function バリデーション通過時に書籍が新規登録される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $data = [
            'title' => 'API登録テスト本',
            'author' => 'API著者',
            'isbn' => '9784111111111',
            'published_date' => '2026-01-01',
            'genre_ids' => [$genre->id],
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'API登録テスト本');

        $this->assertDatabaseHas('books', ['isbn' => '9784111111111']);
    }

    /** @test */
    public function 自身のisbnを除外して更新が成功する(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '9784999999999',
        ]);

        $updateData = [
            'title' => 'API更新後タイトル',
            'author' => $book->author,
            'isbn' => '9784999999999',
            'published_date' => $book->published_date,
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'API更新後タイトル');

    }

    /** @test */
    public function 書籍削除時に関連データも削除される(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => '書籍情報を削除しました。',
            ]);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーは書籍を登録できない(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '未認証登録テスト',
            'author' => 'テスト著者',
            'isbn' => '9784111111111',
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function 未認証ユーザーは書籍を更新できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '未認証更新テスト',
            'author' => $book->author,
            'isbn' => $book->isbn,
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function 未認証ユーザーは書籍を削除できない(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertUnauthorized();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
