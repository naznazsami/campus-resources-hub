<?php

namespace Database\Factories;

use App\Models\StudyResource;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'study_resource_id' => StudyResource::factory(),
            'value' => fake()->randomElement([1, -1]),
        ];
    }
}
