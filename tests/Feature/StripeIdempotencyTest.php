<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DownloadGrant;
use App\Models\StripeEvent;
use App\Models\File;
use App\Mail\OrderReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StripeIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $order;
    protected $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $this->file = File::factory()->create([
            'original_name' => 'Test File.pdf',
            'size_bytes' => 1024,
            'path' => 'files/test-file.pdf'
        ]);

        $this->product = Product::factory()->create([
            'title' => 'Test Product',
            'price_cents' => 1000,
            'is_active' => true
        ]);

        // Attach the file to the product
        $this->product->files()->attach($this->file->id);

        $this->order = Order::factory()->create([
            'user_id' => $this->user->id,
            'total_cents' => 1000,
            'status' => 'pending',
            'stripe_session_id' => 'cs_test_123',
            'stripe_payment_intent' => 'pi_test_123'
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'unit_price_cents' => 1000,
            'line_total_cents' => 1000
        ]);
    }

    private function generateStripeSignature($payload)
    {
        $secret = config('stripe.webhook_secret', 'whsec_test_secret');
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        
        return "t={$timestamp},v1={$signature}";
    }

    public function test_checkout_success_page_is_idempotent_for_paid_orders()
    {
        // Mark order as already paid
        $this->order->update(['status' => 'paid', 'paid_at' => now()]);
        
        // Create existing download grant
        DownloadGrant::factory()->create([
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id
        ]);

        Mail::fake();

        // Hit success page multiple times
        $response1 = $this->actingAs($this->user)
            ->get('/checkout/success?session_id=cs_test_123');
        
        $response2 = $this->actingAs($this->user)
            ->get('/checkout/success?session_id=cs_test_123');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Verify order status remains paid
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);

        // Verify only one download grant exists
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Verify no emails were sent (since order was already paid)
        Mail::assertNothingSent();
    }

    public function test_webhook_event_deduplication_prevents_duplicate_processing()
    {
        Mail::fake();

        $eventId = 'evt_test_12345';
        $payload = [
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                    'payment_status' => 'paid',
                    'amount_total' => 1000,
                    'currency' => 'jpy'
                ]
            ]
        ];

        $headers = [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ];

        // Send the same webhook event twice
        $response1 = $this->postJson('/webhooks/stripe', $payload, $headers);
        $response2 = $this->postJson('/webhooks/stripe', $payload, $headers);

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response2->assertSeeText('Event already processed');

        // Verify event was recorded in stripe_events table
        $this->assertDatabaseHas('stripe_events', [
            'event_id' => $eventId,
            'event_type' => 'checkout.session.completed'
        ]);

        // Verify only one stripe_events record exists
        $eventCount = StripeEvent::where('event_id', $eventId)->count();
        $this->assertEquals(1, $eventCount);

        // Verify order was updated only once
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);

        // Verify only one download grant was created
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Verify only one email was sent
        Mail::assertSent(OrderReceipt::class, 1);
    }

    public function test_webhook_early_return_for_already_paid_orders()
    {
        Mail::fake();

        // Mark order as already paid
        $this->order->update(['status' => 'paid', 'paid_at' => now()]);
        
        // Create existing download grant
        DownloadGrant::factory()->create([
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id
        ]);

        $eventId = 'evt_test_67890';
        $payload = [
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                    'payment_status' => 'paid'
                ]
            ]
        ];

        $headers = [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, $headers);

        $response->assertStatus(200);

        // Verify event was still recorded (for audit trail)
        $this->assertDatabaseHas('stripe_events', [
            'event_id' => $eventId,
            'event_type' => 'checkout.session.completed'
        ]);

        // Verify order status remains paid
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);

        // Verify no additional download grants were created
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Verify no emails were sent (early return)
        Mail::assertNothingSent();
    }

    public function test_payment_intent_succeeded_webhook_idempotency()
    {
        Mail::fake();

        $eventId = 'evt_test_payment_intent';
        $payload = [
            'id' => $eventId,
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'status' => 'succeeded',
                    'amount' => 1000,
                    'currency' => 'jpy'
                ]
            ]
        ];

        $headers = [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ];

        // Send the same webhook twice
        $response1 = $this->postJson('/webhooks/stripe', $payload, $headers);
        $response2 = $this->postJson('/webhooks/stripe', $payload, $headers);

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        $response2->assertSeeText('Event already processed');

        // Verify order was updated only once
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);

        // Verify only one download grant was created
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Verify only one email was sent
        Mail::assertSent(OrderReceipt::class, 1);
    }

    public function test_grant_service_idempotency_with_existing_grants()
    {
        // Create existing download grant
        $existingGrant = DownloadGrant::factory()->create([
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id,
            'product_id' => $this->product->id
        ]);

        // Call GrantService multiple times
        \App\Services\GrantService::createForOrder($this->order);
        \App\Services\GrantService::createForOrder($this->order);

        // Verify only one grant exists (the original one)
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Verify it's the same grant
        $grant = DownloadGrant::where('order_id', $this->order->id)->first();
        $this->assertEquals($existingGrant->id, $grant->id);
    }

    public function test_multiple_different_webhook_events_are_processed_separately()
    {
        Mail::fake();

        $event1Id = 'evt_test_event_1';
        $event2Id = 'evt_test_event_2';

        $payload1 = [
            'id' => $event1Id,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                    'payment_status' => 'paid'
                ]
            ]
        ];

        $payload2 = [
            'id' => $event2Id,
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'status' => 'succeeded'
                ]
            ]
        ];

        $headers1 = ['Stripe-Signature' => $this->generateStripeSignature(json_encode($payload1))];
        $headers2 = ['Stripe-Signature' => $this->generateStripeSignature(json_encode($payload2))];

        // Send first event
        $response1 = $this->postJson('/webhooks/stripe', $payload1, $headers1);
        $response1->assertStatus(200);

        // Send second event (should be processed since it's different)
        $response2 = $this->postJson('/webhooks/stripe', $payload2, $headers2);
        $response2->assertStatus(200);

        // Verify both events were recorded
        $this->assertDatabaseHas('stripe_events', ['event_id' => $event1Id]);
        $this->assertDatabaseHas('stripe_events', ['event_id' => $event2Id]);

        // Verify total event count
        $eventCount = StripeEvent::whereIn('event_id', [$event1Id, $event2Id])->count();
        $this->assertEquals(2, $eventCount);
    }
}