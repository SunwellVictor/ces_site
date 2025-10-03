<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Add a product to the cart.
     */
    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        // Check if product is active
        if (!$product->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available for purchase.'
            ], 400);
        }

        $cart = Session::get('cart', []);
        $productId = $product->id;

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] += $request->quantity;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'title' => $product->title,
                'unit_price_cents' => $product->price_cents,
                'qty' => $request->quantity,
            ];
        }

        Session::put('cart', $cart);

        // For successful operations, redirect with session message
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Request $request, Product $product)
    {
        $cart = Session::get('cart', []);
        $productId = $product->id;

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);

            return redirect()->back()->with('success', 'Product removed from cart successfully!');
        }

        return redirect()->back()->with('error', 'Product not found in cart.');
    }

    /**
     * Display the shopping cart.
     */
    public function show()
    {
        $cart = Session::get('cart', []);
        $cartTotal = 0;

        foreach ($cart as $item) {
            $cartTotal += $item['unit_price_cents'] * $item['qty'];
        }

        return view('cart.index', [
            'cartItems' => $cart,
            'cartTotal' => $cartTotal
        ]);
    }

    /**
     * Update product quantity in cart.
     */
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $cart = Session::get('cart', []);
        $productId = $request->product_id;

        if (isset($cart[$productId])) {
            $cart[$productId]['qty'] = $request->quantity;
            Session::put('cart', $cart);

            $itemTotal = $cart[$productId]['unit_price_cents'] * $cart[$productId]['qty'];
            $cartTotal = array_sum(array_map(function($item) {
                return $item['unit_price_cents'] * $item['qty'];
            }, $cart));

            return response()->json([
                'success' => true,
                'item_total' => $itemTotal,
                'cart_total' => $cartTotal,
                'cart_count' => array_sum(array_column($cart, 'qty'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart.'
        ], 404);
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        Session::forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully!'
        ]);
    }
}
