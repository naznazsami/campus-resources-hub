<?php

namespace Database\Factories;

use App\Enums\ResourceType;
use App\Models\StudyResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudyResource>
 */
class StudyResourceFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(ResourceType::cases())->value,
            'file_path' => 'resources/'.fake()->uuid().'.pdf',
            'original_name' => fake()->slug(3).'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(10_000, 5_000_000),
        ];
    }
}
