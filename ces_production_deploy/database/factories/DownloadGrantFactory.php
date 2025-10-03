<?php

namespace Database\Factories;

use App\Models\DownloadGrant;
use App\Models\User;
use App\Models\Product;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DownloadGrant>
 */
class DownloadGrantFactory extends Factory
{
    protected $model = DownloadGrant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'file_id' => File::factory(),
            'order_id' => null, // Optional
            'max_downloads' => $this->faker->numberBetween(1, 10),
            'downloads_used' => 0,
            'expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
        ];
    }

    /**
     * Indicate that the grant is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the grant is exhausted (all downloads used).
     */
    public function exhausted(): static
    {
        return $this->state(function (array $attributes) {
            $maxDownloads = $this->faker->numberBetween(1, 5);
            return [
                'max_downloads' => $maxDownloads,
                'downloads_used' => $maxDownloads,
            ];
        });
    }

    /**
     * Indicate that the grant has unlimited downloads.
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_downloads' => null,
        ]);
    }

    /**
     * Indicate that the grant never expires.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => null,
        ]);
    }
}
