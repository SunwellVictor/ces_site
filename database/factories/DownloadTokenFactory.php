<?php

namespace Database\Factories;

use App\Models\DownloadToken;
use App\Models\DownloadGrant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DownloadToken>
 */
class DownloadTokenFactory extends Factory
{
    protected $model = DownloadToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grant_id' => DownloadGrant::factory(),
            'token' => (string) Str::uuid(),
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 hour'),
            'used_at' => null,
        ];
    }

    /**
     * Indicate that the token is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 hour', '-1 minute'),
        ]);
    }

    /**
     * Indicate that the token has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Indicate that the token is unused.
     */
    public function unused(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => null,
        ]);
    }

    /**
     * Indicate that the token never expires.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
