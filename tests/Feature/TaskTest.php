<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->token    = $this->user->createToken('auth_token')->plainTextToken;
    }

    public function test_user_can_view_their_tasks(): void
    {
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/tasks');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'completed', 'created_at', 'updated_at']
                ],
                'links',
                'meta'
            ]);
    }

    public function test_user_can_create_a_task(): void
    {
        $taskData = [
            'title'         => 'Test Task',
            'description'   => 'This is a test task',
            'completed'     => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title'         => 'Test Task',
                    'description'   => 'This is a test task',
                    'completed'     => false,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'     => 'Test Task',
            'user_id'   => $this->user->id,
        ]);
    }

    public function test_user_cannot_create_task_without_title(): void
    {
        $taskData = [
            'description'   => 'This is a test task',
            'completed'     => false,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/tasks', $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_view_a_specific_task(): void
    {
        $task = Task::factory()->create([
            'user_id'   => $this->user->id,
            'title'     => 'Specific Task',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id'    => $task->id,
                    'title' => 'Specific Task',
                ]
            ]);
    }

    public function test_user_cannot_view_tasks_of_other_users(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_user_can_update_their_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title'   => 'Original Title',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id'    => $task->id,
                    'title' => 'Updated Title',
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id'    => $task->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_can_toggle_task_completion(): void
    {
        $task = Task::factory()->create([
            'user_id'   => $this->user->id,
            'completed' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson("/api/v1/tasks/{$task->id}/toggle-complete");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id'        => $task->id,
                    'completed' => true,
                ]
            ]);

        $this->assertDatabaseHas('tasks', [
            'id'        => $task->id,
            'completed' => true,
        ]);
    }

    public function test_user_can_delete_their_task(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Task deleted successfully'
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_cannot_delete_tasks_of_other_users(): void
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
}
