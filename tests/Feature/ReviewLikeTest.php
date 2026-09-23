<?php

namespace tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはレビューにいいね・解除ができる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // 1回目の実行（いいね登録）
        $response1 = $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $response1->assertRedirect();
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 2回目の実行（いいね解除）
        $response2 = $this->actingAs($user)->post("/reviews/{$review->id}/like");
        $response2->assertRedirect();
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーはレビューにいいねできない(): void
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('review_likes', [
            'review_id' => $review->id,
        ]);
    }
}
