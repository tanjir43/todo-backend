<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id'       => random_int(1, User::count()),
            'title'         => fake()->sentence(4),
            'description'   => fake()->paragraph(),
            'completed'     => fake()->boolean(20),
        ];
    }

    public function completed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'completed' => true,
            ];
        });
    }
}
