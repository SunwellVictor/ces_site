<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $product;
    protected $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->product = Product::factory()->create([
            'price_cents' => 1000,
            'is_active' => true
        ]);
        
        $this->cartService = new CartService();
        
        // Clear session before each test
        Session::flush();
    }

    public function test_can_add_product_to_cart()
    {
        CartService::add($this->product->id, 2);
        
        $items = CartService::items();
        
        $this->assertArrayHasKey($this->product->id, $items);
        $this->assertEquals(2, $items[$this->product->id]['qty']);
        $this->assertEquals($this->product->id, $items[$this->product->id]['id']);
    }

    public function test_can_add_multiple_quantities_of_same_product()
    {
        CartService::add($this->product->id, 1);
        CartService::add($this->product->id, 2);
        
        $items = CartService::items();
        
        $this->assertEquals(3, $items[$this->product->id]['qty']);
    }

    public function test_can_remove_product_from_cart()
    {
        CartService::add($this->product->id, 2);
        CartService::remove($this->product->id);
        
        $items = CartService::items();
        
        $this->assertArrayNotHasKey($this->product->id, $items);
    }

    public function test_can_add_same_product_multiple_times()
    {
        CartService::add($this->product->id, 2);
        CartService::add($this->product->id, 3);
        
        $items = CartService::items();
        
        $this->assertEquals(5, $items[$this->product->id]['qty']);
    }

    public function test_can_clear_entire_cart()
    {
        $product2 = Product::factory()->create(['price_cents' => 2000, 'is_active' => true]);
        
        CartService::add($this->product->id, 1);
        CartService::add($product2->id, 2);
        
        CartService::clear();
        
        $items = CartService::items();
        
        $this->assertEmpty($items);
    }

    public function test_can_count_total_items_in_cart()
    {
        $product2 = Product::factory()->create(['price_cents' => 2000, 'is_active' => true]);
        
        CartService::add($this->product->id, 2);
        CartService::add($product2->id, 3);
        
        $count = CartService::count();
        
        $this->assertEquals(5, $count);
    }

    public function test_can_check_if_cart_is_empty()
    {
        $this->assertTrue(CartService::isEmpty());
        
        CartService::add($this->product->id, 1);
        
        $this->assertFalse(CartService::isEmpty());
    }

    public function test_can_calculate_total_cart_value()
    {
        $product2 = Product::factory()->create(['price_cents' => 2000, 'is_active' => true]);
        
        CartService::add($this->product->id, 2); // 2 * 1000 = 2000
        CartService::add($product2->id, 1);      // 1 * 2000 = 2000
        
        $total = CartService::totalCents();
        
        $this->assertEquals(4000, $total);
    }

    public function test_total_is_zero_for_empty_cart()
    {
        $total = CartService::totalCents();
        
        $this->assertEquals(0, $total);
    }

    public function test_can_convert_price_cents_to_yen()
    {
        $yen = CartService::priceCentsToYen(1500);
        
        $this->assertEquals('¥15', $yen);
    }

    public function test_can_convert_price_yen_to_cents()
    {
        $cents = CartService::priceYenToCents(15);
        
        $this->assertEquals(1500, $cents);
    }

    public function test_adding_inactive_product_is_ignored()
    {
        $inactiveProduct = Product::factory()->create([
            'price_cents' => 1000,
            'is_active' => false
        ]);
        
        CartService::add($inactiveProduct->id, 1);
        
        $items = CartService::items();
        
        $this->assertArrayNotHasKey($inactiveProduct->id, $items);
    }

    public function test_adding_nonexistent_product_is_ignored()
    {
        CartService::add(99999, 1);
        
        $items = CartService::items();
        
        $this->assertArrayNotHasKey(99999, $items);
    }

    public function test_cart_persists_across_requests()
    {
        CartService::add($this->product->id, 2);
        
        // Simulate new request by creating new CartService instance
        $newCartService = new CartService();
        $items = CartService::items();
        
        $this->assertArrayHasKey($this->product->id, $items);
        $this->assertEquals(2, $items[$this->product->id]['qty']);
    }

    public function test_removing_nonexistent_product_does_not_error()
    {
        CartService::remove(99999);
        
        $items = CartService::items();
        
        $this->assertEmpty($items);
    }

    public function test_adding_zero_quantity_adds_product_with_zero_qty()
    {
        CartService::add($this->product->id, 0);
        
        $items = CartService::items();
        
        $this->assertArrayHasKey($this->product->id, $items);
        $this->assertEquals(0, $items[$this->product->id]['qty']);
    }
}