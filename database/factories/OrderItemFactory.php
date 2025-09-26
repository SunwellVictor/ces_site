<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = $this->faker->numberBetween(100, 10000);
        $qty = $this->faker->numberBetween(1, 5);
        
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'qty' => $qty,
            'unit_price_cents' => $unitPrice,
            'line_total_cents' => $unitPrice * $qty,
        ];
    }
}