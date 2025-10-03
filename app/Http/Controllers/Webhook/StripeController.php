<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StripeEvent;
use App\Services\GrantService;
use App\Mail\OrderReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeController extends Controller
{
    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('stripe.webhook_secret');

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe webhook: Invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook: Invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Check if this event has already been processed (deduplication)
        if (StripeEvent::isProcessed($event['id'])) {
            Log::info('Stripe webhook: Event already processed, skipping', [
                'type' => $event['type'],
                'id' => $event['id']
            ]);
            return response('Event already processed', 200);
        }

        // Log the webhook event
        Log::info('Stripe webhook received', [
            'type' => $event['type'],
            'id' => $event['id']
        ]);

        // Mark event as processed before handling to prevent race conditions
        StripeEvent::markAsProcessed($event['id'], $event['type'], $event['data']->toArray());

        // Handle the event
        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object']);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event['data']['object']);
                break;

            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event['data']['object']);
                break;

            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event['data']['object']);
                break;

            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event['data']['object']);
                break;

            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event['data']['object']);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event['data']['object']);
                break;

            default:
                Log::info('Stripe webhook: Unhandled event type', ['type' => $event['type']]);
        }

        return response('Webhook handled', 200);
    }

    /**
     * Handle successful checkout session completion
     */
    private function handleCheckoutSessionCompleted($session)
    {
        $sessionId = $session['id'];
        
        // Find the order by Stripe session ID
        $order = Order::where('stripe_session_id', $sessionId)->first();
        
        if (!$order) {
            Log::warning('Stripe webhook: Order not found for session', ['session_id' => $sessionId]);
            return;
        }

        // Early return if order is already paid (idempotent)
        if ($order->status === 'paid') {
            Log::info('Stripe webhook: Order already paid, skipping', [
                'order_id' => $order->id,
                'session_id' => $sessionId
            ]);
            return;
        }

        // Update order status to paid
        $order->update([
            'status' => 'paid',
            'stripe_payment_intent' => $session['payment_intent'] ?? null,
            'paid_at' => now(),
        ]);

        // Create download grants for digital products
        GrantService::createForOrder($order);
        
        // Send order receipt email
        Mail::to($order->user->email)->send(new OrderReceipt($order));

        Log::info('Stripe webhook: Checkout session completed', [
            'order_id' => $order->id,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];
        
        // Find the order by payment intent ID
        $order = Order::where('stripe_payment_intent', $paymentIntentId)->first();
        
        if (!$order) {
            Log::warning('Stripe webhook: Order not found for payment intent', ['payment_intent_id' => $paymentIntentId]);
            return;
        }

        // Early return if order is already paid (idempotent)
        if ($order->status === 'paid') {
            Log::info('Stripe webhook: Order already paid, skipping', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntentId
            ]);
            return;
        }

        // Update order status to paid
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Create download grants for digital products
        GrantService::createForOrder($order);
        
        // Send order receipt email
        Mail::to($order->user->email)->send(new OrderReceipt($order));

        Log::info('Stripe webhook: Payment intent succeeded', [
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntentId
        ]);
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        $paymentIntentId = $paymentIntent['id'];
        
        // Find the order by payment intent ID
        $order = Order::where('stripe_payment_intent', $paymentIntentId)->first();
        
        if (!$order) {
            Log::warning('Stripe webhook: Order not found for failed payment intent', ['payment_intent_id' => $paymentIntentId]);
            return;
        }

        // Update order status to failed
        $order->update([
            'status' => 'failed',
            'failure_reason' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
        ]);

        Log::info('Stripe webhook: Payment intent failed', [
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntentId,
            'reason' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown'
        ]);
    }

    /**
     * Handle successful invoice payment (for subscriptions)
     */
    private function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Stripe webhook: Invoice payment succeeded', [
            'invoice_id' => $invoice['id'],
            'subscription_id' => $invoice['subscription'] ?? null
        ]);

        // Handle subscription payment success logic here
        // This could involve extending access, updating subscription status, etc.
    }

    /**
     * Handle failed invoice payment (for subscriptions)
     */
    private function handleInvoicePaymentFailed($invoice)
    {
        Log::info('Stripe webhook: Invoice payment failed', [
            'invoice_id' => $invoice['id'],
            'subscription_id' => $invoice['subscription'] ?? null
        ]);

        // Handle subscription payment failure logic here
        // This could involve sending notifications, updating subscription status, etc.
    }

    /**
     * Handle subscription creation
     */
    private function handleSubscriptionCreated($subscription)
    {
        Log::info('Stripe webhook: Subscription created', [
            'subscription_id' => $subscription['id'],
            'customer_id' => $subscription['customer']
        ]);

        // Handle new subscription logic here
    }

    /**
     * Handle subscription updates
     */
    private function handleSubscriptionUpdated($subscription)
    {
        Log::info('Stripe webhook: Subscription updated', [
            'subscription_id' => $subscription['id'],
            'status' => $subscription['status']
        ]);

        // Handle subscription update logic here
    }

    /**
     * Handle subscription deletion/cancellation
     */
    private function handleSubscriptionDeleted($subscription)
    {
        Log::info('Stripe webhook: Subscription deleted', [
            'subscription_id' => $subscription['id'],
            'customer_id' => $subscription['customer']
        ]);

        // Handle subscription cancellation logic here
    }


}