<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('category', 'supplier', 'variants');

        // Search functionality with proper grouping
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by supplier
        if ($request->has('supplier') && $request->supplier) {
            $query->where('supplier_id', $request->supplier);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->orderBy('name')->paginate(15);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku|max:100',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product = Product::create($validated);

        // Log the action
        AuditLog::create([
            'action' => 'create',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'user_id' => auth()->id(),
            'old_values' => null,
            'new_values' => $product->toJson(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('category', 'supplier', 'variants', 'stockTransactions');

        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:products,sku,' . $product->id . '|max:100',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $oldValues = $product->toArray();
        $product->update($validated);

        // Log the action
        AuditLog::create([
            'action' => 'update',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => $product->toJson(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage (soft delete).
     */
    public function destroy(Product $product)
    {
        $oldValues = $product->toArray();
        $product->delete();

        // Log the action
        AuditLog::create([
            'action' => 'delete',
            'model_type' => Product::class,
            'model_id' => $product->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => null,
        ]);

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Export products to CSV
     */
    public function export()
    {
        $products = Product::with('category', 'supplier')->get();

        $filename = 'products_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://memory', 'w');

        // Write header
        fputcsv($handle, [
            'ID', 'Name', 'SKU', 'Category', 'Supplier',
            'Quantity', 'Price', 'Cost', 'Low Stock Threshold', 'Status'
        ]);

        // Write data
        foreach ($products as $product) {
            fputcsv($handle, [
                $product->id,
                $product->name,
                $product->sku,
                $product->category->name,
                $product->supplier->name,
                $product->quantity,
                $product->price,
                $product->cost,
                $product->low_stock_threshold,
                $product->is_active ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
