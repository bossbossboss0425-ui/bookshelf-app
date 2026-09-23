<?php

namespace tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お気に入りの登録と解除がトグル処理される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // 1回目：お気に入り追加
        $response1 = $this->actingAs($user)->post("/books/{$book->id}/favorites");
        $response1->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 2回目：お気に入り解除
        $response2 = $this->actingAs($user)->post("/books/{$book->id}/favorites");
        $response2->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーはお気に入り一覧画面を閲覧できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'お気に入りに入れた本']);

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('お気に入りに入れた本');
    }

    /** @test */
    public function 未認証ユーザーはお気に入りトグルや一覧にアクセスできない(): void
    {
        $book = Book::factory()->create();

        // 一覧へのアクセスはログインへリダイレクト
        $this->get('/favorites')->assertRedirect('/login');

        // トグル処理もログインへリダイレクト
        $this->post("/books/{$book->id}/favorites")->assertRedirect('/login');
    }
}
