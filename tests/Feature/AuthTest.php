<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'     => 'test@example.com',
            'password'  => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token',
                'token_type',
            ]);
    }

    public function test_user_cannot_login_with_incorrect_password(): void
    {
        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email'     => 'test@example.com',
            'password'  => 'wrong_password',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_logout(): void
    {
        $user   = User::factory()->create();
        $token  = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Successfully logged out']);

        $this->assertCount(0, $user->tokens);
    }

    public function test_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/user');

        $response->assertOk()
            ->assertJson([
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]);
    }
}
