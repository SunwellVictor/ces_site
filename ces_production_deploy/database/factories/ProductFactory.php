<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        
        return [
            'title' => $title,
            // Let the model generate the slug automatically
            'description' => $this->faker->paragraphs(3, true),
            'price_cents' => $this->faker->numberBetween(100, 50000), // $1 to $500
            'currency' => 'JPY',
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_digital' => $this->faker->boolean(70), // 70% chance of being digital
            'seo_title' => $this->faker->optional()->sentence(),
            'seo_description' => $this->faker->optional()->text(160),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product is digital.
     */
    public function digital(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_digital' => true,
        ]);
    }

    /**
     * Indicate that the product is physical.
     */
    public function physical(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_digital' => false,
        ]);
    }
}
