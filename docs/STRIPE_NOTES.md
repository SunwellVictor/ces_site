# Stripe Integration Notes

## Environment Variables

### Required Keys
```bash
# Production Keys (live environment)
STRIPE_PUBLIC=pk_live_YOUR_STRIPE_PUBLIC_KEY
STRIPE_SECRET=sk_live_YOUR_STRIPE_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET

# Test Keys (development/staging)
STRIPE_PUBLIC=pk_test_YOUR_STRIPE_TEST_PUBLIC_KEY
STRIPE_SECRET=sk_test_YOUR_STRIPE_TEST_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_TEST_WEBHOOK_SECRET
```

### Configuration
- **Currency**: JPY (Japanese Yen) - configured in `config/stripe.php`
- **Download Grant Defaults**: 5 downloads, 2-year expiration
- **Webhook Endpoint**: `/webhooks/stripe`

## Webhook Events Handled

The system handles the following Stripe webhook events in `app/Http/Controllers/Webhook/StripeController.php`:

### Payment Events
- **`checkout.session.completed`**: Processes successful checkout sessions
  - Updates order status to 'paid'
  - Creates download grants via GrantService
  - Sends order receipt email
  - Idempotent: skips if order already paid

- **`payment_intent.succeeded`**: Handles successful payment intents
  - Updates order status to 'paid'
  - Creates download grants
  - Sends confirmation email
  - Idempotent: early return for paid orders

- **`payment_intent.payment_failed`**: Handles failed payments
  - Updates order status to 'failed'
  - Logs failure for investigation

### Subscription Events (Future Use)
- **`invoice.payment_succeeded`**: Subscription payment success
- **`invoice.payment_failed`**: Subscription payment failure
- **`customer.subscription.created`**: New subscription
- **`customer.subscription.updated`**: Subscription changes
- **`customer.subscription.deleted`**: Subscription cancellation

### Event Deduplication
- Uses `stripe_events` table to prevent duplicate processing
- Checks `StripeEvent::isProcessed($eventId)` before handling
- Marks events as processed with `StripeEvent::markAsProcessed()`

## Test Cards

### Successful Payments
```
4242424242424242  # Visa
4000056655665556  # Visa (debit)
5555555555554444  # Mastercard
2223003122003222  # Mastercard (2-series)
4000002500003155  # Visa (prepaid)
```

### Failed Payments
```
4000000000000002  # Generic decline
4000000000009995  # Insufficient funds
4000000000009987  # Lost card
4000000000009979  # Stolen card
4000000000000069  # Expired card
```

### 3D Secure Authentication
```
4000002760003184  # 3D Secure required
4000002500003155  # 3D Secure optional
```

### International Cards
```
4000000400000008  # US
4000001240000000  # Canada
4000000760000002  # Brazil
4000001560000002  # India
```

## Webhook Testing & Replay

### Local Development Setup

1. **Install Stripe CLI**
   ```bash
   # macOS
   brew install stripe/stripe-cli/stripe
   
   # Login to Stripe
   stripe login
   ```

2. **Forward Webhooks to Local Server**
   ```bash
   # Start local server first
   php artisan serve --host=127.0.0.1 --port=8000
   
   # Forward webhooks (new terminal)
   stripe listen --forward-to localhost:8000/webhooks/stripe
   ```

3. **Get Webhook Secret**
   ```bash
   # The CLI will display the webhook secret
   # Add to .env as STRIPE_WEBHOOK_SECRET=whsec_...
   ```

### Webhook Replay Commands

#### Replay Specific Event
```bash
# Replay a checkout session completed event
stripe events resend evt_1234567890

# Replay with specific webhook endpoint
stripe events resend evt_1234567890 --webhook-endpoint we_1234567890
```

#### Trigger Test Events
```bash
# Trigger a test payment success
stripe trigger payment_intent.succeeded

# Trigger a test checkout session
stripe trigger checkout.session.completed

# Trigger a test payment failure
stripe trigger payment_intent.payment_failed
```

#### Test Idempotency
```bash
# Send the same event multiple times to test deduplication
stripe events resend evt_1234567890
stripe events resend evt_1234567890  # Should be ignored
```

### Production Webhook Setup

1. **Create Webhook Endpoint in Stripe Dashboard**
   - URL: `https://clarkenglish.com/webhooks/stripe`
   - Events: Select all payment-related events
   - Copy webhook secret to production `.env`

2. **Test Production Webhooks**
   ```bash
   # Use Stripe CLI to test production endpoint
   stripe listen --forward-to https://clarkenglish.com/webhooks/stripe
   ```

## Troubleshooting

### Common Issues

1. **Invalid Signature Errors**
   - Check `STRIPE_WEBHOOK_SECRET` is correct
   - Ensure webhook endpoint URL matches exactly
   - Verify request is coming from Stripe

2. **Duplicate Processing**
   - Check `stripe_events` table for event deduplication
   - Verify `StripeEvent::isProcessed()` logic
   - Review webhook retry settings in Stripe dashboard

3. **Order Not Found**
   - Verify `stripe_session_id` is stored correctly
   - Check order creation in checkout flow
   - Ensure session metadata includes order ID

### Debugging Commands

```bash
# Check recent webhook events
php artisan tinker
>>> App\Models\StripeEvent::latest()->take(10)->get()

# Check order payment status
>>> App\Models\Order::where('stripe_session_id', 'cs_test_...')->first()

# Check download grants for order
>>> App\Models\DownloadGrant::where('order_id', 123)->get()
```

### Log Monitoring

- **Webhook Events**: `storage/logs/laravel.log`
- **Payment Processing**: Search for "Stripe webhook" entries
- **Error Tracking**: Monitor 400/500 responses to webhook endpoint

## Security Notes

- Never commit live Stripe keys to version control
- Use test keys for all development/staging environments
- Webhook endpoints should validate Stripe signatures
- Monitor webhook endpoint for unusual traffic patterns
- Regularly rotate webhook secrets in production