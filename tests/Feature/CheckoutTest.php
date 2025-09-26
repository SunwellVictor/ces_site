<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Order;
use App\Models\DownloadGrant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $product;
    protected $file;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->file = File::factory()->create();
        $this->product = Product::factory()->create([
            'price_cents' => 1000, // $10.00
            'is_active' => true
        ]);
        $this->product->files()->attach($this->file);
    }

    public function test_user_can_add_product_to_cart()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('cart.add', $this->product), [
            'quantity' => 2
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cart = CartService::items();
        $this->assertCount(1, $cart);
        $this->assertEquals(2, $cart[$this->product->id]['qty']);
        $this->assertEquals($this->product->id, $cart[$this->product->id]['id']);
    }

    public function test_user_can_view_cart()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 1);

        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertViewHas('cartItems');
        $response->assertSee($this->product->title);
    }

    public function test_user_can_remove_product_from_cart()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 1);
        
        $response = $this->delete(route('cart.remove', $this->product));

        $response->assertRedirect();
        $cart = CartService::items();
        $this->assertEmpty($cart);
    }

    public function test_user_can_initiate_checkout()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 1);

        $response = $this->post(route('checkout.create'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'checkout_url'
        ]);
        
        $responseData = $response->json();
        $this->assertStringContainsString('checkout.stripe.com', $responseData['checkout_url']);
    }

    public function test_checkout_creates_pending_order()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 2);

        $this->post(route('checkout.create'));

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_cents' => 2000, // 2 * $10.00
            'status' => 'pending'
        ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order->stripe_session_id);
        
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'unit_price_cents' => 1000,
            'line_total_cents' => 2000
        ]);
    }

    public function test_checkout_success_page_displays_order_details()
    {
        $this->actingAs($this->user);
        
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'stripe_session_id' => 'cs_test_123'
        ]);

        $response = $this->get(route('checkout.success', ['session_id' => 'cs_test_123']));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.success');
        $response->assertViewHas('order');
        $response->assertSee('Payment Successful');
    }

    public function test_checkout_success_clears_cart()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 1);
        
        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'paid',
            'stripe_session_id' => 'cs_test_123'
        ]);

        $this->get(route('checkout.success', ['session_id' => 'cs_test_123']));

        $cart = CartService::items();
        $this->assertEmpty($cart);
    }

    public function test_checkout_cancel_page_preserves_cart()
    {
        $this->actingAs($this->user);
        
        // Add product to cart
        CartService::add($this->product->id, 1);

        $response = $this->get(route('checkout.cancel'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.cancel');
        
        // Cart should still contain items
        $cart = CartService::items();
        $this->assertCount(1, $cart);
    }

    public function test_guest_cannot_access_checkout()
    {
        $response = $this->post(route('checkout.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_empty_cart_cannot_checkout()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('checkout.create'));

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Cart is empty']);
    }

    public function test_inactive_product_cannot_be_added_to_cart()
    {
        $this->actingAs($this->user);
        
        $inactiveProduct = Product::factory()->create(['is_active' => false]);

        $response = $this->post(route('cart.add', $inactiveProduct), [
            'quantity' => 1
        ]);

        $response->assertStatus(400);
        $cart = CartService::items();
        $this->assertEmpty($cart);
    }
}