<?php

namespace Database\Factories;

use App\Models\ProviderHealth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProviderHealth>
 */
class ProviderHealthFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProviderHealth::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => $this->faker->randomElement(['openai', 'cloudflare', 'cohere']),
            'is_healthy' => $this->faker->boolean,
            'last_check' => $this->faker->dateTime,
            'response_time_ms' => $this->faker->numberBetween(10, 500),
            'error_count' => $this->faker->numberBetween(0, 10),
            'success_count' => $this->faker->numberBetween(0, 100),
            'last_error' => $this->faker->optional()->sentence,
            'configuration' => [],
        ];
    }
}