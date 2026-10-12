<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReadingPlanReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 予定日の3日前にリマインダー通知を送信する(): void
    {
        Notification::fake();

        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($plan) {
                $data = $notification->toArray($plan->user);

                return $data['timing'] === 'three_days_before'
                    && $data['reading_plan_id'] === $plan->id;
            }
        );

        $this->travelBack();
    }

    /** @test */
    public function 予定日当日にリマインダー通知を送信する(): void
    {
        Notification::fake();

        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($plan) {
                $data = $notification->toArray($plan->user);

                return $data['timing'] === 'on_due_date'
                    && $data['reading_plan_id'] === $plan->id;
            }
        );

        $this->travelBack();
    }

    /** @test */
    public function 期限を過ぎた未完了の読書計画を自動失効させる(): void
    {
        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => 'in_progress',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'expired',
        ]);

        $this->travelBack();
    }

    /** @test */
    public function 完了済みの読書計画は自動失効しない(): void
    {
        $this->travelTo(now()->startOfDay());

        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
            'status' => 'completed',
        ]);

        $this->artisan('app:send-reading-plan-reminders')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);

        $this->travelBack();
    }
}
