<?php

namespace tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューは投稿ユーザーに属する(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    /** @test */
    public function レビューは対象の書籍に属する(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    /** @test */
    public function レビューはいいねをしたユーザーのリストを持つ(): void
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();

        $review->likedByUsers()->attach($user->id);

        $this->assertTrue($review->likedByUsers->contains($user));
    }
}
