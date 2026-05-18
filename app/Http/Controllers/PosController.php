<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\StockTransaction;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        $categories = Category::where('is_active', true)->get();
        $settings   = StoreSetting::asArray();

        return view('pos.index', compact('categories', 'settings'));
    }

    /**
     * Return current cart as JSON (used by the live UI).
     */
    public function getCart()
    {
        $cart     = session()->get('pos_cart', []);
        $settings = StoreSetting::asArray();
        $taxRate  = (float) ($settings['tax_rate'] ?? 0);

        $subtotal   = $this->calculateCartSubtotal($cart);
        $taxAmount  = round($subtotal * $taxRate / 100, 2);
        $total      = $subtotal + $taxAmount;

        return response()->json([
            'success'    => true,
            'cart'       => array_values($cart),
            'subtotal'   => $subtotal,
            'tax_rate'   => $taxRate,
            'tax_amount' => $taxAmount,
            'total'      => $total,
            'tax_label'  => $settings['tax_label'] ?? 'VAT',
            'currency'   => $settings['currency_symbol'] ?? '₱',
        ]);
    }

    /**
     * Search products for POS (AJAX endpoint).
     */
    public function search(Request $request)
    {
        $search     = $request->get('q', '');
        $categoryId = $request->get('category_id');

        $query = Product::where('is_active', true)
            ->with('category', 'variants');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->orderBy('name')->limit(50)->get();

        return response()->json([
            'success' => true,
            'data'    => $products->map(fn ($p) => $this->formatProduct($p)),
        ]);
    }

    /**
     * Lookup product by exact SKU (for barcode/quick-add).
     */
    public function lookupSku(Request $request)
    {
        $sku     = trim($request->get('sku', ''));
        $product = Product::where('is_active', true)
            ->where('sku', $sku)
            ->with('variants')
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatProduct($product),
        ]);
    }

    /**
     * Add item to cart (AJAX). Returns updated cart JSON.
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'variant_id' => 'nullable|exists:product_variants,id',
        ]);

        $cart    = session()->get('pos_cart', []);
        $product = Product::with('variants')->find($validated['product_id']);
        $variant = $validated['variant_id'] ? ProductVariant::find($validated['variant_id']) : null;

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        // Stock check
        $stockSource = $variant ?? $product;
        if ($stockSource->quantity < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Only {$stockSource->quantity} units in stock",
            ], 400);
        }

        $cartKey = $variant
            ? "product_{$product->id}_variant_{$variant->id}"
            : "product_{$product->id}";

        $price = (float) $product->price + ($variant ? (float) $variant->price_modifier : 0);

        if (isset($cart[$cartKey])) {
            $newQty = $cart[$cartKey]['quantity'] + $validated['quantity'];
            if ($stockSource->quantity < $newQty) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$stockSource->quantity} units available",
                ], 400);
            }
            $cart[$cartKey]['quantity'] = $newQty;
        } else {
            $cart[$cartKey] = [
                'cart_key'     => $cartKey,
                'product_id'   => $product->id,
                'variant_id'   => $variant ? $variant->id : null,
                'product_name' => $product->name,
                'sku'          => $product->sku,
                'variant_name' => $variant ? "{$variant->type}: {$variant->value}" : null,
                'quantity'     => $validated['quantity'],
                'unit_price'   => $price,
                'stock'        => $stockSource->quantity,
            ];
        }

        session()->put('pos_cart', $cart);

        return $this->getCart();
    }

    /**
     * Remove item from cart (AJAX).
     */
    public function removeFromCart(Request $request)
    {
        $cartKey = $request->get('cart_key');
        $cart    = session()->get('pos_cart', []);

        unset($cart[$cartKey]);
        session()->put('pos_cart', $cart);

        return $this->getCart();
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

        if (!isset($cart[$validated['cart_key']])) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $item    = $cart[$validated['cart_key']];
        $product = Product::find($item['product_id']);
        $variant = $item['variant_id'] ? ProductVariant::find($item['variant_id']) : null;
        $avail   = $variant ? $variant->quantity : $product->quantity;

        if ($validated['quantity'] > $avail) {
            return response()->json([
                'success' => false,
                'message' => "Only {$avail} units available",
            ], 400);
        }

        $cart[$validated['cart_key']]['quantity'] = $validated['quantity'];
        session()->put('pos_cart', $cart);

        return $this->getCart();
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart()
    {
        session()->forget('pos_cart');
        return response()->json(['success' => true, 'message' => 'Cart cleared']);
    }

    /**
     * Process checkout and create transaction.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'discount'         => 'nullable|numeric|min:0',
            'discount_type'    => 'nullable|in:percentage,fixed',
            'payment_method'   => 'required|in:cash,card,check,bank_transfer',
            'customer_name'    => 'nullable|string|max:255',
            'cash_tendered'    => 'nullable|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
        ]);

        $cart = session()->get('pos_cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        // Load tax settings
        $settings  = StoreSetting::asArray();
        $taxRate   = (float) ($settings['tax_rate'] ?? 0);

        try {
            DB::beginTransaction();

            // Calculate subtotal
            $subtotal = $this->calculateCartSubtotal($cart);

            // Apply discount
            $discountAmount = 0;
            if (!empty($validated['discount']) && $validated['discount'] > 0) {
                if ($validated['discount_type'] === 'percentage') {
                    $discountAmount = round($subtotal * $validated['discount'] / 100, 2);
                } else {
                    $discountAmount = min((float) $validated['discount'], $subtotal);
                }
            }

            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount     = round($subtotal * $taxRate / 100, 2);
            $total         = $subtotal + $taxAmount - $discountAmount;

            // Calculate change for cash payments
            $cashTendered  = null;
            $changeAmount  = null;
            if ($validated['payment_method'] === 'cash') {
                $cashTendered = (float) ($validated['cash_tendered'] ?? $total);
                $changeAmount = max(0, $cashTendered - $total);
            }

            // Create transaction
            $transaction = PosTransaction::create([
                'user_id'          => auth()->id(),
                'transaction_number' => PosTransaction::generateTransactionNumber(),
                'customer_name'    => $validated['customer_name'] ?? null,
                'subtotal'         => $subtotal,
                'discount'         => $discountAmount,
                'tax_rate'         => $taxRate,
                'tax_amount'       => $taxAmount,
                'total'            => $total,
                'payment_method'   => $validated['payment_method'],
                'cash_tendered'    => $cashTendered,
                'change_amount'    => $changeAmount,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'completed',
                'transaction_date' => now(),
            ]);

            // Add items and update stock
            foreach ($cart as $cartItem) {
                $product = Product::find($cartItem['product_id']);
                $variant = $cartItem['variant_id']
                    ? ProductVariant::find($cartItem['variant_id'])
                    : null;

                if (!$product) continue;

                $itemSubtotal = $cartItem['quantity'] * $cartItem['unit_price'];

                PosTransactionItem::create([
                    'pos_transaction_id' => $transaction->id,
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'product_name'       => $cartItem['product_name'],
                    'sku'                => $cartItem['sku'],
                    'quantity'           => $cartItem['quantity'],
                    'unit_price'         => $cartItem['unit_price'],
                    'discount'           => 0,
                    'subtotal'           => $itemSubtotal,
                ]);

                // Deduct stock
                if ($variant) {
                    $variant->decrement('quantity', $cartItem['quantity']);
                } else {
                    $product->decrement('quantity', $cartItem['quantity']);
                }

                // Low stock alert — only if product hits reorder level
                if ($product->quantity <= $product->low_stock_threshold) {
                    // Write directly to notifications table without relying on a model class
                    $alertUsers = \App\Models\User::whereHas('role', fn($q) =>
                        $q->whereIn('name', ['admin', 'store_manager'])
                    )->get();
                    foreach ($alertUsers as $alertUser) {
                        try {
                            \DB::table('notifications')->insert([
                                'user_id'       => $alertUser->id,
                                'type'          => 'low_stock',
                                'title'         => 'Low Stock: ' . $product->name,
                                'message'       => $product->name . ' is low (' . $product->quantity . ' ' . ($product->unit ?? 'units') . ' left).',
                                'related_model' => \App\Models\Product::class,
                                'related_id'    => $product->id,
                                'is_read'       => false,
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ]);
                        } catch (\Throwable) {
                            // Notifications table may not exist — non-critical, skip silently
                        }
                    }
                }

                // Stock transaction log
                StockTransaction::create([
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'type'               => 'stock_out',
                    'quantity'           => $cartItem['quantity'],
                    'reference'          => $transaction->transaction_number,
                    'user_id'            => auth()->id(),
                    'notes'              => 'POS Sale - ' . $transaction->transaction_number,
                ]);
            }

            DB::commit();
            session()->forget('pos_cart');

            return response()->json([
                'success'    => true,
                'message'    => 'Transaction completed',
                'receipt_url' => route('pos.receipt', $transaction),
                'transaction_number' => $transaction->transaction_number,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Checkout error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display transaction receipt.
     */
    public function receipt(PosTransaction $transaction)
    {
        $transaction->load('items.product', 'items.variant', 'user');
        $settings = StoreSetting::asArray();
        return view('pos.receipt', compact('transaction', 'settings'));
    }

    /**
     * Generate and download PDF receipt in browser.
     */
    public function receiptPdf(PosTransaction $transaction)
    {
        $transaction->load('items.product', 'items.variant', 'user');
        $settings = StoreSetting::asArray();

        $pdf = Pdf::loadView('pos.receipt-pdf', compact('transaction', 'settings'))
            ->setPaper('A4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 15)
            ->setOption('margin-right', 15);

        return $pdf->stream("receipt-{$transaction->transaction_number}.pdf");
    }

    /**
     * Void a transaction (admin/manager only).
     */
    public function void(PosTransaction $transaction)
    {
        if ($transaction->status === 'voided') {
            return response()->json(['success' => false, 'message' => 'Already voided'], 400);
        }

        DB::beginTransaction();
        try {
            // Restore stock
            foreach ($transaction->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('quantity', $item->quantity);
                } elseif ($item->product) {
                    $item->product->increment('quantity', $item->quantity);
                }

                StockTransaction::create([
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'type'               => 'stock_in',
                    'quantity'           => $item->quantity,
                    'reference'          => 'VOID-' . $transaction->transaction_number,
                    'user_id'            => auth()->id(),
                    'notes'              => 'Voided transaction',
                ]);
            }

            $transaction->update(['status' => 'voided']);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction voided']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all transactions (store manager / admin view).
     */
    public function transactions(Request $request)
    {
        $query = PosTransaction::with('user', 'items')
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $settings     = StoreSetting::asArray();

        return view('pos.transactions', compact('transactions', 'settings'));
    }

    /**
     * Cashier's own sales history — filtered to the logged-in cashier's transactions.
     */
    public function mySales(Request $request)
    {
        $query = PosTransaction::with(['items'])
            ->where('user_id', auth()->id())
            ->orderBy('transaction_date', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transaction_date', '<=', $request->to_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $settings     = StoreSetting::asArray();
        $totalToday   = PosTransaction::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDate('transaction_date', today())
            ->sum('total');
        $countToday   = PosTransaction::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDate('transaction_date', today())
            ->count();

        return view('pos.my-sales', compact('transactions', 'settings', 'totalToday', 'countToday'));
    }

    // ===================== Helpers =====================

    private function calculateCartSubtotal(array $cart): float
    {
        return (float) collect($cart)->sum(fn ($item) => $item['quantity'] * $item['unit_price']);
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id'                 => $product->id,
            'name'               => $product->name,
            'sku'                => $product->sku,
            'price'              => (float) $product->price,
            'quantity'           => $product->quantity,
            'total_quantity'     => $product->getTotalVariantQuantity(),
            'unit'               => $product->unit,
            'category'           => $product->category?->name,
            'image'              => $product->image
                ? asset('storage/' . $product->image)
                : null,
            'has_variants'       => $product->variants->count() > 0,
            'variants'           => $product->variants->map(fn ($v) => [
                'id'             => $v->id,
                'name'           => $v->type . ': ' . $v->value,
                'sku'            => $v->sku,
                'quantity'       => $v->quantity,
                'price_modifier' => (float) $v->price_modifier,
            ]),
        ];
    }
}
