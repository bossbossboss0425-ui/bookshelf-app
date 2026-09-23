<?php

namespace tests\Unit\Models;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーは複数の投稿書籍を持つ(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->books->contains($book));
    }

    /** @test */
    public function ユーザーは複数のレビューを持つ(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->reviews->contains($review));
    }

    /** @test */
    public function ユーザーはお気に入り書籍を所有できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->assertTrue($user->favoriteBooks->contains($book));
    }
}
