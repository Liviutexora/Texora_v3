<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserVisit;
use Illuminate\Support\Str;

class UserVisitFactory extends Factory
{
    protected $model = UserVisit::class;

    public function definition()
    {
        $createdAt = $this->faker->dateTimeBetween('-12 months', 'now');
        $userId = $this->faker->boolean(50) ? $this->faker->numberBetween(1, 154) : null;

        return [
            'ip_address' => $this->faker->ipv4,
            'user_id' => $userId,
            'user_agent' => $this->faker->userAgent,
            'session_id' => Str::uuid(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
