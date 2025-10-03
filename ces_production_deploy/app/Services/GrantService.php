<?php

namespace App\Services;

use App\Models\Order;
use App\Models\DownloadGrant;
use Illuminate\Support\Facades\Log;

class GrantService
{
    /**
     * Create download grants for an order (idempotent).
     */
    public static function createForOrder(Order $order): void
    {
        // Load order items with products and their files
        $order->load(['orderItems.product.files']);

        foreach ($order->orderItems as $item) {
            if (!$item->product || !$item->product->files()->exists()) {
                continue;
            }

            // Create download grants for each file attached to the product
            foreach ($item->product->files as $file) {
                // Use firstOrCreate for idempotency - won't create duplicates
                $grant = DownloadGrant::firstOrCreate([
                    'user_id' => $order->user_id,
                    'product_id' => $item->product->id,
                    'file_id' => $file->id,
                    'order_id' => $order->id,
                ], [
                    'expires_at' => now()->addYears(config('stripe.download_grant_defaults.expiration_years', 2)),
                    'max_downloads' => config('stripe.download_grant_defaults.max_downloads', 5),
                    'downloads_used' => 0,
                ]);

                if ($grant->wasRecentlyCreated) {
                    Log::info('Download grant created', [
                        'grant_id' => $grant->id,
                        'user_id' => $order->user_id,
                        'file_id' => $file->id,
                        'order_id' => $order->id,
                    ]);
                }
            }
        }
    }
}