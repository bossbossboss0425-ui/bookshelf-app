<?php

namespace tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_p01_書籍一覧が_jso_n形式で取得できる(): void
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
    public function a_p02_指定_i_dの書籍詳細が取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $book->id);
    }

    /** @test */
    public function a_p02_存在しない_i_dの場合は404エラーレスポンスが返る(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => '指定された書籍が見つかりませんでした。',
            ]);
    }

    /** @test */
    public function a_p03_バリデーション通過時に書籍が新規登録される(): void
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
    public function a_p04_自身の_isb_nを除外して更新が成功する(): void
    {
        $book = Book::factory()->create(['isbn' => '9784999999999']);

        $updateData = [
            'title' => 'API更新後タイトル',
            'author' => $book->author,
            'isbn' => '9784999999999', // 同一のISBN
            'published_date' => $book->published_date,
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'API更新後タイトル');
    }

    /** @test */
    public function a_p05_書籍削除時に関連データも削除される(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => '書籍情報を削除しました。']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
