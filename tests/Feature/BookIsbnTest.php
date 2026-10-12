<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookIsbnTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function isb_nから書籍情報を取得できる(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['山田太郎', '佐藤花子'],
                            'publishedDate' => '2020-04',
                            'description' => 'テスト用の説明です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/book.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784478025819');

        $response->assertOk()
            ->assertJson([
                'title' => 'テスト書籍',
                'author' => '山田太郎, 佐藤花子',
                'published_date' => '2020-04-01T00:00:00Z',
                'description' => 'テスト用の説明です。',
                'image_url' => 'https://example.com/book.jpg',
            ]);

        Http::assertSent(function ($request) {
            return str_contains(
                $request->url(),
                'www.googleapis.com/books/v1/volumes'
            ) && $request['q'] === 'isbn:9784478025819';
        });
    }

    /** @test */
    public function ハイフン付き_isb_nでも検索できる(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/978-4-478-02581-9');

        $response->assertOk()
            ->assertJsonPath('title', 'テスト書籍');

        Http::assertSent(function ($request) {
            return $request['q'] === 'isbn:9784478025819';
        });
    }

    /** @test */
    public function isb_nが13桁でない場合は400を返す(): void
    {
        Http::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/12345');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'ISBNは13桁の半角数字で入力してください。',
            ]);

        Http::assertNothingSent();
    }

    /** @test */
    public function 該当する書籍がない場合は404を返す(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784478025819');

        $response->assertNotFound()
            ->assertJson([
                'error' => '該当する書籍が見つかりませんでした。',
            ]);
    }

    /** @test */
    public function google_books_ap_iが失敗した場合は500を返す(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'error' => [
                    'message' => 'Service unavailable',
                ],
            ], 503),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784478025819');

        $response->assertStatus(500)
            ->assertJson([
                'error' => '書籍情報の取得に失敗しました。',
            ]);
    }

    /** @test */
    public function 著者や出版日や画像がない場合も書籍情報を返せる(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => '情報が少ない書籍',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/books/isbn/9784478025819');

        $response->assertOk()
            ->assertJson([
                'title' => '情報が少ない書籍',
                'author' => '',
                'published_date' => '',
                'description' => '',
                'image_url' => '',
            ]);
    }

    /** @test */
    public function 未ログインでは_isb_n検索にアクセスできない(): void
    {
        Http::fake();

        $this->getJson('/books/isbn/9784478025819')
            ->assertUnauthorized();

        Http::assertNothingSent();
    }
}
