<?php

namespace Database\Factories;

use App\Models\UserMemory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserMemory>
 */
class UserMemoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserMemory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'thing_to_remember' => $this->faker->paragraph,
            'title' => $this->faker->sentence,
            'document_type' => 'Memory',
            'project_name' => $this->faker->word,
            'tags' => $this->faker->words(3),
        ];
    }
}