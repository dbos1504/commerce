<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * Display the user's cart.
     */
    public function index()
    {
        $user = auth()->user();
        $cart = $user->cart()->with(['items.product'])->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
            $cart->load(['items.product']);
        }

        // Format total as string for frontend
        $cart->total = number_format($cart->total, 2, '.', '');

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $product = Product::findOrFail($request->product_id);

        // Check stock availability
        if ($product->stock_quantity < $request->quantity) {
            return back()->withErrors([
                'quantity' => 'Insufficient stock. Only ' . $product->stock_quantity . ' items available.',
            ]);
        }

        DB::transaction(function () use ($user, $product, $request) {
            // Get or create cart
            $cart = $user->cart()->firstOrCreate(['user_id' => $user->id]);

            // Check if item already exists in cart
            $cartItem = $cart->items()->where('product_id', $product->id)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;

                // Check stock again with new quantity
                if ($product->stock_quantity < $newQuantity) {
                    throw new \Exception('Insufficient stock. Only ' . $product->stock_quantity . ' items available.');
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                ]);
            }

            // Check for low stock and dispatch job
            if ($product->isLowStock()) {
                \App\Jobs\LowStockNotification::dispatch($product);
            }
        });

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Verify cart belongs to authenticated user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        $product = $cartItem->product;

        // Check stock availability
        if ($product->stock_quantity < $request->quantity) {
            return back()->withErrors([
                'quantity' => 'Insufficient stock. Only ' . $product->stock_quantity . ' items available.',
            ]);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        // Check for low stock
        if ($product->isLowStock()) {
            \App\Jobs\LowStockNotification::dispatch($product);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart.
     */
    public function remove(CartItem $cartItem)
    {
        // Verify cart belongs to authenticated user
        if ($cartItem->cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cartItem->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    /**
     * Checkout - Create order from cart.
     */
    public function checkout()
    {
        $user = auth()->user();
        $cart = $user->cart()->with(['items.product'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => 'Your cart is empty.',
            ]);
        }

        // Validate stock availability
        foreach ($cart->items as $item) {
            if ($item->product->stock_quantity < $item->quantity) {
                return redirect()->route('cart.index')->withErrors([
                    'stock' => 'Insufficient stock for ' . $item->product->name . '. Only ' . $item->product->stock_quantity . ' available.',
                ]);
            }
        }

        DB::transaction(function () use ($user, $cart) {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $cart->total,
            ]);

            // Create order items and update stock
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                // Update product stock
                $item->product->decrement('stock_quantity', $item->quantity);

                // Check for low stock after update
                $item->product->refresh();
                if ($item->product->isLowStock()) {
                    \App\Jobs\LowStockNotification::dispatch($item->product);
                }
            }

            // Clear cart
            $cart->items()->delete();
        });

        return redirect()->route('cart.index')->with('success', 'Order placed successfully!');
    }
}
