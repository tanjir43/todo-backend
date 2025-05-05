<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TaskResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    private TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'completed'         => 'nullable|boolean',
            'search'            => 'nullable|string|max:255',
            'sort_by'           => 'nullable|string|in:title,created_at,updated_at',
            'sort_direction'    => 'nullable|string|in:asc,desc',
            'per_page'          => 'nullable|integer|min:5|max:100',
        ]);

        $tasks = $this->taskService->getUserTasks($request->user(), $filters);
        return TaskResource::collection($tasks);
    }

    public function store(TaskStoreRequest $request): JsonResponse|TaskResource
    {
        $task = $this->taskService->createTask(
            $request->user(),
            $request->validated()
        );

        return new TaskResource($task);
    }

    public function show(Task $task): JsonResponse|TaskResource
    {
        if (! Gate::allows('view', $task)) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }

        return new TaskResource($task);
    }

    public function update(TaskUpdateRequest $request, Task $task): JsonResponse|TaskResource
    {
        if (! Gate::allows('update', $task)) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $task = $this->taskService->updateTask(
            $task,
            $request->validated()
        );

        return new TaskResource($task);
    }

    public function toggleComplete(Task $task): JsonResponse|TaskResource
    {
        if (! Gate::allows('update', $task)) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $task = $this->taskService->toggleTaskCompletion($task);

        return new TaskResource($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        if (! Gate::allows('delete', $task)) {
            return response()->json([
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $this->taskService->deleteTask($task);

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
