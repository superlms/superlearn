<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_responds(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok');
    }

    public function test_user_can_register_and_gets_a_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Student',
            'email' => 'Student@Example.com',
            'password' => 'secret-pass-123',
            'device_name' => 'test-phone',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']])
            ->assertJsonPath('user.email', 'student@example.com')
            ->assertJsonMissingPath('user.password');

        $this->assertDatabaseHas('users', ['email' => 'student@example.com']);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'secret-pass-123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_user_can_login_with_correct_password(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'right-password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'A@example.com',
            'password' => 'right-password',
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'right-password']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'a@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_me_needs_a_token_and_returns_the_user(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_logout_revokes_the_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
