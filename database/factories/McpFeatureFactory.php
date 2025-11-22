<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\McpFeature>
 */
class McpFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['tool', 'resource', 'prompt']);
        $name = fake()->unique()->slug(2);

        return [
            'type' => $type,
            'name' => $name,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'class_name' => null,
            'handler_code' => null,
            'schema_definition' => null,
            'arguments_definition' => null,
            'is_system' => false,
            'is_active_by_default' => true,
        ];
    }

    /**
     * Indicate that the feature is a system feature.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    /**
     * Indicate that the feature is a custom feature.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => false,
        ]);
    }

    /**
     * Indicate that the feature is a tool.
     */
    public function tool(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tool',
        ]);
    }

    /**
     * Indicate that the feature is a resource.
     */
    public function resource(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'resource',
        ]);
    }

    /**
     * Indicate that the feature is a prompt.
     */
    public function prompt(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'prompt',
        ]);
    }
}
