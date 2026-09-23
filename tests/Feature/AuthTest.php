<?php

namespace tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録画面が表示される(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test */
    public function 正しい情報で会員登録が成功する(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $this->assertAuthenticated();
        $response->assertRedirect('/books');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function ログイン画面が表示される(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function 正しいクレデンシャルでログインできる(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/books');
    }

    /** @test */
    public function ログイン済みユーザーがログイン画面にアクセスするとリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/books');
    }

    /** @test */
    public function ログアウトができる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
