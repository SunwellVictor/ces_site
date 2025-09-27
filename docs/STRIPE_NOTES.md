# Stripe Integration Notes

## Environment Variables

Add these to your `.env` file:

```env
STRIPE_PUBLIC=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

- **STRIPE_PUBLIC**: Publishable key for frontend (safe to expose)
- **STRIPE_SECRET**: Secret key for server-side API calls (keep secure)
- **STRIPE_WEBHOOK_SECRET**: Webhook endpoint secret for signature verification

## Test Cards

Use these test card numbers in development:

| Card Number | Brand | Result |
|-------------|-------|--------|
| 4242424242424242 | Visa | Success |
| 4000000000000002 | Visa | Declined |
| 4000000000009995 | Visa | Insufficient funds |
| 5555555555554444 | Mastercard | Success |
| 4000000000000119 | Visa | Processing error |

**Test Details:**
- Use any future expiry date (e.g., 12/34)
- Use any 3-digit CVC (e.g., 123)
- Use any valid ZIP code (e.g., 12345)

## Events Handled

The system processes these Stripe webhook events:

### Payment Events
- `checkout.session.completed` - Creates download grants after successful payment
- `payment_intent.succeeded` - Confirms payment completion
- `payment_intent.payment_failed` - Handles failed payments

### Invoice Events
- `invoice.payment_succeeded` - Processes successful invoice payments
- `invoice.payment_failed` - Handles failed invoice payments

### Subscription Events
- `customer.subscription.created` - Sets up new subscriptions
- `customer.subscription.updated` - Updates existing subscriptions
- `customer.subscription.deleted` - Cancels subscriptions

## Event Replay

To replay webhook events during development:

1. **Stripe CLI** (recommended):
   ```bash
   stripe listen --forward-to localhost:8000/webhooks/stripe
   stripe trigger checkout.session.completed
   ```

2. **Dashboard Replay**:
   - Go to Stripe Dashboard → Developers → Webhooks
   - Select your webhook endpoint
   - Find the event in "Recent deliveries"
   - Click "Resend" to replay

3. **Manual Testing**:
   - Use the test suite: `php artisan test tests/Feature/StripeWebhookTest.php`
   - All webhook scenarios are covered with proper test data

## Configuration

Stripe settings are configured in `config/stripe.php`:

- **Currency**: JPY (Japanese Yen)
- **Download Grant Defaults**:
  - Max downloads: 5 per purchase
  - Expires: 2 years from purchase

## Security Notes

- Webhook signatures are verified using `STRIPE_WEBHOOK_SECRET`
- All events are deduplicated using `StripeEvent` model
- Download grants use `firstOrCreate()` for idempotency
- Order status is checked before processing to prevent duplicates

## Troubleshooting

**Common Issues:**
- 400 errors: Check webhook signature and secret
- Duplicate grants: Verify event deduplication is working
- Missing events: Ensure webhook endpoint is configured in Stripe Dashboard

**Logs:**
- Check `storage/logs/laravel.log` for webhook processing errors
- Enable Stripe debug mode in development for detailed API logs