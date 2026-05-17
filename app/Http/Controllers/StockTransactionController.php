<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Http\Request;

class StockTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = StockTransaction::with('product', 'user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->paginate(25)->withQueryString();
        $products     = Product::orderBy('name')->get(['id', 'name']);

        return view('stock-transactions.index', compact('transactions', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'type'       => 'required|in:stock_in,stock_out,adjustment',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $before  = $product->quantity;

        if ($validated['type'] === 'stock_out' && $before < $validated['quantity']) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available: ' . $before])->withInput();
        }

        if ($validated['type'] === 'stock_in') {
            $product->increment('quantity', $validated['quantity']);
        } elseif ($validated['type'] === 'stock_out') {
            $product->decrement('quantity', $validated['quantity']);
        } elseif ($validated['type'] === 'adjustment') {
            $product->update(['quantity' => $validated['quantity']]);
        }

        $product->refresh();

        StockTransaction::create([
            'product_id'      => $product->id,
            'user_id'         => auth()->id(),
            'type'            => $validated['type'],
            'quantity'        => $validated['quantity'],
            'quantity_before' => $before,
            'quantity_after'  => $product->quantity,
            'reference'       => 'TXN-' . now()->format('YmdHis'),
            'notes'           => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Stock transaction recorded successfully.');
    }
}
