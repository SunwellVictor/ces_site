<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DownloadGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['price_cents' => 1000]);
        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_cents' => 1000,
            'status' => 'pending'
        ]);
    }

    public function test_order_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->order->user);
        $this->assertEquals($this->user->id, $this->order->user->id);
    }

    public function test_order_has_many_order_items()
    {
        $orderItem = OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id
        ]);

        $this->assertTrue($this->order->orderItems->contains($orderItem));
        $this->assertCount(1, $this->order->orderItems);
    }

    public function test_order_has_many_download_grants()
    {
        $file = File::factory()->create();
        $downloadGrant = DownloadGrant::factory()->create([
            'order_id' => $this->order->id,
            'file_id' => $file->id,
            'user_id' => $this->user->id
        ]);

        $this->assertTrue($this->order->downloadGrants->contains($downloadGrant));
        $this->assertCount(1, $this->order->downloadGrants);
    }

    public function test_order_scope_pending()
    {
        Order::factory()->create(['status' => 'paid']);
        Order::factory()->create(['status' => 'failed']);
        
        $pendingOrders = Order::pending()->get();
        
        $this->assertCount(1, $pendingOrders);
        $this->assertEquals('pending', $pendingOrders->first()->status);
    }

    public function test_order_scope_paid()
    {
        $paidOrder = Order::factory()->create(['status' => 'paid']);
        Order::factory()->create(['status' => 'failed']);
        
        $paidOrders = Order::paid()->get();
        
        $this->assertCount(1, $paidOrders);
        $this->assertEquals('paid', $paidOrders->first()->status);
    }

    public function test_order_scope_failed()
    {
        Order::factory()->create(['status' => 'paid']);
        $failedOrder = Order::factory()->create(['status' => 'failed']);
        
        $failedOrders = Order::failed()->get();
        
        $this->assertCount(1, $failedOrders);
        $this->assertEquals('failed', $failedOrders->first()->status);
    }

    public function test_order_scope_refunded()
    {
        Order::factory()->create(['status' => 'paid']);
        $refundedOrder = Order::factory()->create(['status' => 'refunded']);
        
        $refundedOrders = Order::refunded()->get();
        
        $this->assertCount(1, $refundedOrders);
        $this->assertEquals('refunded', $refundedOrders->first()->status);
    }

    public function test_order_fillable_attributes()
    {
        $order = new Order();
        $fillable = $order->getFillable();
        
        $expectedFillable = [
            'user_id',
            'total_cents',
            'currency',
            'status',
            'stripe_session_id',
            'stripe_payment_intent'
        ];
        
        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_order_total_cents_is_integer()
    {
        $order = Order::factory()->create(['total_cents' => 1500]);
        
        $this->assertIsInt($order->total_cents);
        $this->assertEquals(1500, $order->total_cents);
    }

    public function test_order_currency_defaults_to_jpy()
    {
        $order = Order::factory()->create();
        
        $this->assertEquals('jpy', $order->currency);
    }

    public function test_order_status_defaults_to_pending()
    {
        $order = Order::factory()->create();
        
        $this->assertEquals('pending', $order->status);
    }

    public function test_order_can_be_created_with_stripe_identifiers()
    {
        $order = Order::factory()->create([
            'stripe_session_id' => 'cs_test_123',
            'stripe_payment_intent' => 'pi_test_123'
        ]);
        
        $this->assertEquals('cs_test_123', $order->stripe_session_id);
        $this->assertEquals('pi_test_123', $order->stripe_payment_intent);
    }

    public function test_order_timestamps_are_set()
    {
        $order = Order::factory()->create();
        
        $this->assertNotNull($order->created_at);
        $this->assertNotNull($order->updated_at);
    }
}