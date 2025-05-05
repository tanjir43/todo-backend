<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskService = new TaskService();
        $this->user = User::factory()->create();
    }

    public function test_get_user_tasks_returns_paginated_results(): void
    {
        Task::factory()->count(15)->create([
            'user_id' => $this->user->id
        ]);

        $result = $this->taskService->getUserTasks($this->user, ['per_page' => 10]);

        $this->assertEquals(10, $result->count());
        $this->assertEquals(15, $result->total());
    }

    public function test_get_user_tasks_filters_by_completed_status(): void
    {
        Task::factory()->count(5)->create([
            'user_id'   => $this->user->id,
            'completed' => true
        ]);

        Task::factory()->count(3)->create([
            'user_id'   => $this->user->id,
            'completed' => false
        ]);

        $completedTasks     = $this->taskService->getUserTasks($this->user, ['completed' => true]);
        $incompleteTasks    = $this->taskService->getUserTasks($this->user, ['completed' => false]);

        $this->assertEquals(5, $completedTasks->total());
        $this->assertEquals(3, $incompleteTasks->total());
    }

    public function test_get_user_tasks_can_search_by_title(): void
    {
        Task::factory()->create([
            'user_id'   => $this->user->id,
            'title'     => 'Learn Laravel',
        ]);

        Task::factory()->create([
            'user_id'   => $this->user->id,
            'title'     => 'Learn Vue.js',
        ]);

        $result = $this->taskService->getUserTasks($this->user, ['search' => 'Laravel']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Learn Laravel', $result->first()->title);
    }

    public function test_create_task_creates_a_new_task_for_user(): void
    {
        $taskData = [
            'title'         => 'New Task',
            'description'   => 'Task description',
            'completed'     => false,
        ];

        $task = $this->taskService->createTask($this->user, $taskData);

        $this->assertEquals('New Task', $task->title);
        $this->assertEquals('Task description', $task->description);
        $this->assertEquals(false, $task->completed);
        $this->assertEquals($this->user->id, $task->user_id);

        $this->assertDatabaseHas('tasks', [
            'title'     => 'New Task',
            'user_id'   => $this->user->id,
        ]);
    }

    public function test_update_task_updates_task_properties(): void
    {
        $task = Task::factory()->create([
            'user_id'       => $this->user->id,
            'title'         => 'Original Title',
            'description'   => 'Original description',
        ]);

        $updatedTask = $this->taskService->updateTask($task, [
            'title'         => 'Updated Title',
            'description'   => 'Updated description',
        ]);

        $this->assertEquals('Updated Title', $updatedTask->title);
        $this->assertEquals('Updated description', $updatedTask->description);

        $this->assertDatabaseHas('tasks', [
            'id'            => $task->id,
            'title'         => 'Updated Title',
            'description'   => 'Updated description',
        ]);
    }

    public function test_toggle_task_completion_toggles_completed_status(): void
    {
        $task = Task::factory()->create([
            'user_id'   => $this->user->id,
            'completed' => false,
        ]);

        $updatedTask = $this->taskService->toggleTaskCompletion($task);
        $this->assertTrue($updatedTask->completed);

        $updatedTask = $this->taskService->toggleTaskCompletion($updatedTask);
        $this->assertFalse($updatedTask->completed);
    }

    public function test_delete_task_removes_task_from_database(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $result = $this->taskService->deleteTask($task);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
