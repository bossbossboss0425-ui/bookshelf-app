<?php

namespace tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => '素晴らしい本でした。',
        ]);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function レビュー投稿者本人のみが編集・削除できる(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $author->id]);

        // 他人が削除を試みると403
        $this->actingAs($otherUser)->delete("/reviews/{$review->id}")->assertStatus(403);

        // 本人は削除可能
        $this->actingAs($author)->delete("/reviews/{$review->id}")->assertRedirect("/books/{$review->book_id}");
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function レビューのいいねがトグル動作する(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 1回目の実行で「いいね追加」
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseHas('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);

        // 2回目の実行で「いいね解除」
        $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $this->assertDatabaseMissing('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }
}
