<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Add a product to the cart.
     */
    public static function add(int $productId, int $qty = 1): bool
    {
        $product = Product::where('id', $productId)
            ->where('is_active', true)
            ->first();

        if (!$product) {
            return false;
        }

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += $qty;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'title' => $product->title,
                'unit_price_cents' => $product->price_cents,
                'qty' => $qty,
            ];
        }

        Session::put('cart', $cart);
        return true;
    }

    /**
     * Remove a product from the cart.
     */
    public static function remove(int $productId): bool
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
            return true;
        }

        return false;
    }

    /**
     * Get all cart items.
     */
    public static function items(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Get the total cart value in cents.
     */
    public static function totalCents(): int
    {
        $cart = Session::get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['unit_price_cents'] * $item['qty'];
        }

        return $total;
    }

    /**
     * Clear the entire cart.
     */
    public static function clear(): void
    {
        Session::forget('cart');
    }

    /**
     * Get the total number of items in the cart.
     */
    public static function count(): int
    {
        $cart = Session::get('cart', []);
        return array_sum(array_column($cart, 'qty'));
    }

    /**
     * Check if the cart is empty.
     */
    public static function isEmpty(): bool
    {
        $cart = Session::get('cart', []);
        return empty($cart);
    }

    /**
     * Convert price from cents to yen (formatted).
     */
    public static function priceCentsToYen(int $cents): string
    {
        return '¥' . number_format($cents / 100);
    }

    /**
     * Convert price from yen to cents.
     */
    public static function priceYenToCents(float $yen): int
    {
        return (int) round($yen * 100);
    }
}