<?php

namespace tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍が紐付いているジャンルは削除が制限される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        // 削除されずにエラーメッセージがセッションに入る
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }

    /** @test */
    public function 書籍が紐付いていないジャンルは正常に削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect('/genres');
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }
}
