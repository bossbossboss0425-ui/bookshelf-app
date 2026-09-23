<?php

namespace tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビュー平均評価順に_to_p10が表示されレビュー無しの書籍は表示されない(): void
    {
        $user = User::factory()->create();

        // レビューなしの書籍
        $noReviewBook = Book::factory()->create(['title' => 'レビュー無しの本']);

        // 高評価の書籍
        $highRatedBook = Book::factory()->create(['title' => '高評価の本']);
        Review::factory()->create(['book_id' => $highRatedBook->id, 'user_id' => $user->id, 'rating' => 5]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);
        $response->assertSee('高評価の本');
        $response->assertDontSee('レビュー無しの本');
    }
}
