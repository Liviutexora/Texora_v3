<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'license_number' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'bio' => fake()->paragraph(),
            'experience_years' => fake()->numberBetween(1, 30),
        ];
    }
}
