<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function getUserTasks(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->tasks();

        if (isset($filters['completed']) && $filters['completed'] !== null) {
            $query->where('completed', $filters['completed']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sortField      = $filters['sort_by'] ?? 'created_at';
        $sortDirection  = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function createTask(User $user, array $data): Task
    {
        return $user->tasks()->create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function toggleTaskCompletion(Task $task): Task
    {
        $task->completed = !$task->completed;
        $task->save();
        return $task;
    }

    public function deleteTask(Task $task): bool
    {
        return $task->delete();
    }
}
