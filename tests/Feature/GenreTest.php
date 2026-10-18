<?php

namespace Tests\Feature;

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
    public function ジャンル一覧を表示できる(): void
    {
        Genre::create(['name' => 'プログラミング']);
        Genre::create(['name' => 'ビジネス']);

        $response = $this->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewIs('genres.index');
        $response->assertViewHas('genres', function ($genres) {
            return $genres->contains('name', 'プログラミング')
                && $genres->contains('name', 'ビジネス');
        });
    }

    /** @test */
    public function ジャンルに紐付く書籍一覧を表示できる(): void
    {
        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $book = Book::factory()->create([
            'title' => 'Laravel入門',
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');
        $response->assertViewHas('genre', function ($viewGenre) use ($genre) {
            return $viewGenre->id === $genre->id;
        });
        $response->assertViewHas('books', function ($books) use ($book) {
            return $books->contains('id', $book->id);
        });
    }

    /** @test */
    public function ジャンル作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));

        $response->assertOk();
        $response->assertViewIs('genres.create');
    }

    /** @test */
    public function ジャンルを新規登録できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ミステリー',
        ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを作成しました。');

        $this->assertDatabaseHas('genres', [
            'name' => 'ミステリー',
        ]);
    }

    /** @test */
    public function ジャンル名が空の場合は登録できない(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('genres', [
            'name' => '',
        ]);
    }

    /** @test */
    public function 同じジャンル名は重複登録できない(): void
    {
        $user = User::factory()->create();

        Genre::create([
            'name' => 'ミステリー',
        ]);

        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ミステリー',
        ]);

        $response->assertSessionHasErrors('name');

        $this->assertSame(
            1,
            Genre::where('name', 'ミステリー')->count()
        );
    }

    /** @test */
    public function ジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'ミステリー',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.edit', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.edit');
        $response->assertViewHas('genre', function ($viewGenre) use ($genre) {
            return $viewGenre->id === $genre->id;
        });
    }

    /** @test */
    public function ジャンル名を更新できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'ミステリー',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '歴史',
            ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンル名を更新しました。');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '歴史',
        ]);
    }

    /** @test */
    public function ジャンル名が空の場合は更新できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'ミステリー',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'ミステリー',
        ]);
    }

    /** @test */
    public function 既存のジャンル名と重複する名前には更新できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'ミステリー',
        ]);

        Genre::create([
            'name' => '歴史',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '歴史',
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'ミステリー',
        ]);
    }

    /** @test */
    public function 書籍が紐付いていないジャンルは正常に削除できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create([
            'name' => 'プログラミング',
        ]);

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを削除しました。');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }
}
