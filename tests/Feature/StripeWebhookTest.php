<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DownloadGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderReceipt;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $file;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->file = File::factory()->create();
        $this->product = Product::factory()->create([
            'price_cents' => 1000,
            'is_active' => true
        ]);
        $this->product->files()->attach($this->file);
        
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

    public function test_checkout_session_completed_webhook_updates_order_status()
    {
        Mail::fake();

        $payload = [
            'id' => 'evt_test_checkout_123',
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

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ]);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);
        
        // Check that download grants were created
        $this->assertDatabaseHas('download_grants', [
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id
        ]);

        // Check that email was sent
        Mail::assertSent(OrderReceipt::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_payment_intent_succeeded_webhook_updates_order_status()
    {
        Mail::fake();

        $payload = [
            'id' => 'evt_test_payment_123',
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

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ]);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);
        
        // Check that download grants were created
        $this->assertDatabaseHas('download_grants', [
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id
        ]);

        // Check that email was sent
        Mail::assertSent(OrderReceipt::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    public function test_payment_intent_payment_failed_webhook_updates_order_status()
    {
        $payload = [
            'id' => 'evt_test_failed_123',
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'status' => 'requires_payment_method',
                    'amount' => 1000,
                    'currency' => 'jpy'
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ]);

        $response->assertStatus(200);

        $this->order->refresh();
        $this->assertEquals('failed', $this->order->status);
        
        // Check that no download grants were created
        $this->assertDatabaseMissing('download_grants', [
            'order_id' => $this->order->id
        ]);
    }

    public function test_webhook_with_invalid_signature_is_rejected()
    {
        $payload = [
            'id' => 'evt_test_grants_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                    'payment_status' => 'paid'
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => 'invalid_signature'
        ]);

        $response->assertStatus(400);
        
        // Order should remain unchanged
        $this->order->refresh();
        $this->assertEquals('pending', $this->order->status);
    }

    public function test_webhook_for_nonexistent_order_is_handled_gracefully()
    {
        $payload = [
            'id' => 'evt_test_nonexistent_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_nonexistent',
                    'payment_intent' => 'pi_nonexistent',
                    'payment_status' => 'paid'
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ]);

        $response->assertStatus(200);
    }

    public function test_duplicate_webhook_events_are_handled_idempotently()
    {
        Mail::fake();

        $payload = [
            'id' => 'evt_test_duplicate_123',
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

        // Send the same webhook twice
        $response1 = $this->postJson('/webhooks/stripe', $payload, $headers);
        $response2 = $this->postJson('/webhooks/stripe', $payload, $headers);

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // Order should only be updated once
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->status);
        
        // Download grants should only be created once
        $grantCount = DownloadGrant::where('order_id', $this->order->id)->count();
        $this->assertEquals(1, $grantCount);

        // Email should only be sent once
        Mail::assertSent(OrderReceipt::class, 1);
    }

    public function test_webhook_creates_download_grants_for_all_order_files()
    {
        // Add another file to the product
        $secondFile = File::factory()->create();
        $this->product->files()->attach($secondFile);

        $payload = [
            'id' => 'evt_test_multiple_files_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'payment_intent' => 'pi_test_123',
                    'payment_status' => 'paid'
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/stripe', $payload, [
            'Stripe-Signature' => $this->generateStripeSignature(json_encode($payload))
        ]);

        $response->assertStatus(200);

        // Check that download grants were created for both files
        $this->assertDatabaseHas('download_grants', [
            'order_id' => $this->order->id,
            'file_id' => $this->file->id,
            'user_id' => $this->user->id
        ]);
        
        $this->assertDatabaseHas('download_grants', [
            'order_id' => $this->order->id,
            'file_id' => $secondFile->id,
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Generate a mock Stripe signature for testing.
     */
    private function generateStripeSignature(string $payload): string
    {
        $secret = config('stripe.webhook_secret', 'test_webhook_secret');
        $timestamp = time();
        $signedPayload = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);
        
        return "t={$timestamp},v1={$signature}";
    }
}