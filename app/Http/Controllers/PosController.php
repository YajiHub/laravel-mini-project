<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class PosController extends Controller
{
    /**
     * Display the POS interface with shopping cart.
     */
    public function index()
    {
        $categories = \App\Models\Category::where('is_active', true)->get();
        $cart = session()->get('pos_cart', []);
        $cartTotal = $this->calculateCartTotal($cart);

        return view('pos.index', compact('categories', 'cart', 'cartTotal'));
    }

    /**
     * Search products for POS (AJAX endpoint).
     */
    public function search(Request $request)
    {
        $search = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $query = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->with('category', 'supplier', 'variants');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'quantity_available' => $product->quantity,
                    'has_variants' => $product->variants->count() > 0,
                    'variants' => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->type . ': ' . $variant->value,
                            'sku' => $variant->sku,
                            'quantity' => $variant->quantity,
                            'price_modifier' => $variant->price_modifier,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Add item to cart (AJAX).
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $cart = session()->get('pos_cart', []);
        $product = Product::find($validated['product_id']);
        $variant = $validated['variant_id'] ? ProductVariant::find($validated['variant_id']) : null;

        if (!$product || $product->quantity < $validated['quantity']) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock'], 400);
        }

        if ($variant && $variant->quantity < $validated['quantity']) {
            return response()->json(['success' => false, 'message' => 'Insufficient variant stock'], 400);
        }

        // Create unique cart key for product-variant combination
        $cartKey = $variant ? "product_{$product->id}_variant_{$variant->id}" : "product_{$product->id}";

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $validated['quantity'];
        } else {
            $price = $product->price + ($variant ? $variant->price_modifier : 0);
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'variant_id' => $variant ? $variant->id : null,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'variant_name' => $variant ? "{$variant->type}: {$variant->value}" : null,
                'quantity' => $validated['quantity'],
                'unit_price' => $price,
                'cart_key' => $cartKey,
            ];
        }

        session()->put('pos_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'cart_count' => count($cart),
            'cart_total' => $this->calculateCartTotal($cart),
        ]);
    }

    /**
     * Remove item from cart (AJAX).
     */
    public function removeFromCart(Request $request)
    {
        $cartKey = $request->get('cart_key');
        $cart = session()->get('pos_cart', []);

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            session()->put('pos_cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'cart_total' => $this->calculateCartTotal($cart),
        ]);
    }

    /**
     * Update item quantity in cart (AJAX).
     */
    public function updateQuantity(Request $request)
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('pos_cart', []);

        if (isset($cart[$validated['cart_key']])) {
            // Check stock availability
            $item = $cart[$validated['cart_key']];
            $product = Product::find($item['product_id']);
            $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;

            $availableQty = $variant ? $variant->quantity : $product->quantity;

            if ($validated['quantity'] > $availableQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$availableQty} items available",
                ], 400);
            }

            $cart[$validated['cart_key']]['quantity'] = $validated['quantity'];
            session()->put('pos_cart', $cart);
        }

        return response()->json([
            'success' => true,
            'cart_total' => $this->calculateCartTotal($cart),
        ]);
    }

    /**
     * Process checkout and create transaction.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'discount' => 'nullable|numeric|min:0|max:100',
            'discount_type' => 'nullable|in:percentage,fixed',
            'payment_method' => 'required|in:cash,card,check,bank_transfer',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $cart = session()->get('pos_cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'Cart is empty');
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = $this->calculateCartTotal($cart);
            $discount = 0;

            if ($validated['discount']) {
                if ($validated['discount_type'] === 'percentage') {
                    $discount = ($subtotal * $validated['discount']) / 100;
                } else {
                    $discount = $validated['discount'];
                }
            }

            $total = $subtotal - $discount;

            // Create transaction record
            $transaction = PosTransaction::create([
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'customer_name' => $validated['customer_name'],
                'notes' => $validated['notes'],
                'transaction_date' => now(),
            ]);

            // Add items and update stock
            foreach ($cart as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $variant = $cartItem['variant_id'] ? ProductVariant::find($cartItem['variant_id']) : null;

                // Create transaction item
                PosTransactionItem::create([
                    'pos_transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $cartItem['unit_price'],
                    'discount' => 0, // Can be extended for per-item discounts
                    'subtotal' => $cartItem['quantity'] * $cartItem['unit_price'],
                ]);

                // Deduct from product quantity
                $product->decrement('quantity', $cartItem['quantity']);

                // Deduct from variant quantity if applicable
                if ($variant) {
                    $variant->decrement('quantity', $cartItem['quantity']);
                }

                // Record stock transaction
                StockTransaction::create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'type' => 'out',
                    'quantity' => $cartItem['quantity'],
                    'reference' => "POS-{$transaction->id}",
                    'user_id' => auth()->id(),
                    'notes' => 'POS Transaction',
                ]);
            }

            DB::commit();

            // Clear cart
            session()->forget('pos_cart');

            return redirect()->route('pos.receipt', $transaction)->with('success', 'Transaction completed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display transaction receipt.
     */
    public function receipt(PosTransaction $transaction)
    {
        $transaction->load('items.product', 'items.variant', 'user');
        return view('pos.receipt', compact('transaction'));
    }

    /**
     * Generate PDF receipt.
     */
    public function receiptPdf(PosTransaction $transaction)
    {
        $transaction->load('items.product', 'items.variant', 'user');

        $html = view('pos.receipt-pdf', compact('transaction'))->render();
        $pdf = PDF::loadHTML($html);

        return $pdf->download("receipt-{$transaction->id}.pdf");
    }

    /**
     * Get all transactions (admin view).
     */
    public function transactions(Request $request)
    {
        $query = PosTransaction::with('user', 'items.product')
            ->orderBy('transaction_date', 'desc');

        // Date range filter
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }

        // Payment method filter
        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $transactions = $query->paginate(20);

        return view('pos.transactions', compact('transactions'));
    }

    /**
     * Calculate cart total.
     */
    private function calculateCartTotal($cart)
    {
        return collect($cart)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });
    }
}
