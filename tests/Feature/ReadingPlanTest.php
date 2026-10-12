<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログイン済みユーザーは自分の読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get('/reading-plans');

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.index');
        $response->assertSee($book->title);
    }

    /** @test */
    public function ログイン済みユーザーは読書計画を新規作成できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function ログイン済みユーザーは読書計画を編集できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $newDate = now()->addDays(14)->toDateString();

        $response = $this->actingAs($user)->put(
            "/reading-plans/{$plan->id}",
            [
                'target_date' => $newDate,
            ]
        );

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $newDate.' 00:00:00',
        ]);
    }

    /** @test */
    public function ログイン済みユーザーは読書計画を完了できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(
            "/reading-plans/{$plan->id}/complete"
        );

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull($plan->fresh()->completed_at);
    }

    /** @test */
    public function ログイン済みユーザーは読書計画を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->delete(
            "/reading-plans/{$plan->id}"
        );

        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    /** @test */
    public function 他のユーザーは読書計画を編集できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($otherUser)->put(
            "/reading-plans/{$plan->id}",
            [
                'target_date' => now()->addDays(14)->toDateString(),
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'user_id' => $owner->id,
            'target_date' => $plan->getRawOriginal('target_date'),
        ]);
    }

    /** @test */
    public function 他のユーザーは読書計画を削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($otherUser)->delete(
            "/reading-plans/{$plan->id}"
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'user_id' => $owner->id,
        ]);
    }

    /** @test */
    public function ゲストは読書計画一覧にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 読書計画一覧をステータスで絞り込める(): void
    {
        $user = User::factory()->create();

        $book1 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '進行中の本',
        ]);

        $book2 = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '完了した本',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => now()->subDays(1)->toDateString(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)
            ->get('/reading-plans?status=in_progress');

        $response->assertOk();
        $response->assertSee('進行中の本');
        $response->assertDontSee('完了した本');
    }

    /** @test */
    public function 読書計画の作成時に予定日が未入力ならエラーになる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->from('/reading-plans/create')
            ->post('/reading-plans', [
                'book_id' => $book->id,
                'target_date' => '',
            ]);

        $response->assertRedirect('/reading-plans/create');
        $response->assertSessionHasErrors('target_date');

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 読書計画の期限変更時に予定日が未入力ならエラーになる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $originalDate = $plan->getRawOriginal('target_date');

        $response = $this->actingAs($user)
            ->from("/reading-plans/{$plan->id}/edit")
            ->put("/reading-plans/{$plan->id}", [
                'target_date' => '',
            ]);

        $response->assertRedirect("/reading-plans/{$plan->id}/edit");
        $response->assertSessionHasErrors('target_date');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $originalDate,
        ]);
    }

    /** @test */
    public function 読書計画の期限変更時に不正な日付形式ならエラーになる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(7)->toDateString(),
            'status' => 'in_progress',
        ]);

        $originalDate = $plan->getRawOriginal('target_date');

        $response = $this->actingAs($user)
            ->from("/reading-plans/{$plan->id}/edit")
            ->put("/reading-plans/{$plan->id}", [
                'target_date' => 'not-a-date',
            ]);

        $response->assertRedirect("/reading-plans/{$plan->id}/edit");
        $response->assertSessionHasErrors('target_date');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $originalDate,
        ]);
    }
}
