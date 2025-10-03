<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        .download-link {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank you for your purchase!</h1>
        <p>Your order has been successfully processed and your download links are now available.</p>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>
        <p><strong>Order Number:</strong> #{{ $order->id }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
        <p><strong>Payment Method:</strong> Stripe</p>
        
        @if($order->stripe_payment_intent_id)
        <p><strong>Payment ID:</strong> {{ $order->stripe_payment_intent_id }}</p>
        @endif

        <h3>Items Purchased</h3>
        @foreach($order->orderItems as $item)
        <div class="item">
            <strong>{{ $item->product->name }}</strong><br>
            Quantity: {{ $item->qty }}<br>
            Price: ¥{{ number_format($item->unit_price_cents / 100) }}<br>
            Subtotal: ¥{{ number_format($item->line_total_cents / 100) }}
        </div>
        @endforeach

        <div class="total">
            Total: ¥{{ number_format($order->total_cents / 100) }}
        </div>
    </div>

    <div style="text-align: center;">
        <a href="{{ $downloadUrl }}" class="download-link">Access Your Downloads</a>
    </div>

    <div class="order-details">
        <h3>Download Information</h3>
        <p>Your digital products are now available for download in your account. Each download link:</p>
        <ul>
            <li>Is valid for {{ config('stripe.download_grant_defaults.expiration_years', 2) }} years from purchase</li>
            <li>Can be used up to {{ config('stripe.download_grant_defaults.max_downloads', 5) }} times</li>
            <li>Is secure and personalized to your account</li>
        </ul>
        <p><strong>Important:</strong> Please save your files to a secure location after downloading.</p>
    </div>

    <div class="footer">
        <p>If you have any questions about your order or need assistance, please contact our support team.</p>
        <p>Thank you for your business!</p>
        <p><em>{{ config('app.name') }}</em></p>
    </div>
</body>
</html>