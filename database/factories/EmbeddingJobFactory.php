<?php

namespace Database\Factories;

use App\Models\EmbeddingJob;
use App\Models\UserMemory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmbeddingJob>
 */
class EmbeddingJobFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmbeddingJob::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'memory_id' => UserMemory::factory(),
            'provider' => $this->faker->randomElement(['openai', 'cloudflare', 'cohere']),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'attempts' => $this->faker->numberBetween(0, 3),
            'max_attempts' => 3,
            'error_message' => $this->faker->optional()->sentence,
            'payload' => [],
        ];
    }
}