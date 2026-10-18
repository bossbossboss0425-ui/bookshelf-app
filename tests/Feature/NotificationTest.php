<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ログインユーザーの通知一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $user->notifications()->create([
            'id' => '33333333-3333-4333-8333-333333333333',
            'type' => 'test',
            'data' => ['message' => 'テスト通知'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');
        $response->assertViewHas('notifications');
    }

    /** @test */
    public function 自分の通知を既読にできる(): void
    {
        $user = User::factory()->create();

        $notification = $user->notifications()->create([
            'id' => '11111111-1111-4111-8111-111111111111',
            'type' => 'test',
            'data' => ['message' => 'テスト通知'],
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success', '通知を既読にしました。');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    /** @test */
    public function 他のユーザーの通知は既読にできない(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $notification = $anotherUser->notifications()->create([
            'id' => '22222222-2222-4222-8222-222222222222',
            'type' => 'test',
            'data' => ['message' => '別ユーザーの通知'],
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }
}
