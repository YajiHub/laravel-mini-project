<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\ProductVariant;
use App\Models\AuditLog;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'sku'                 => 'required|string|unique:products,sku|max:100',
            'category_id'         => 'required|exists:categories,id',
            'supplier_id'         => 'required|exists:suppliers,id',
            'quantity'            => 'required|integer|min:0',
            'price'               => 'required|numeric|min:0',
            'cost'                => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'unit'                => 'nullable|string|max:50',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'           => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        AuditLog::create([
            'action'     => 'create',
            'model_type' => Product::class,
            'model_id'   => $product->id,
            'user_id'    => auth()->id(),
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
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'sku'                 => 'required|string|unique:products,sku,' . $product->id . '|max:100',
            'category_id'         => 'required|exists:categories,id',
            'supplier_id'         => 'required|exists:suppliers,id',
            'quantity'            => 'required|integer|min:0',
            'price'               => 'required|numeric|min:0',
            'cost'                => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'unit'                => 'nullable|string|max:50',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_image'        => 'nullable|boolean',
            'is_active'           => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } elseif ($request->boolean('remove_image') && $product->image) {
            Storage::disk('public')->delete($product->image);
            $validated['image'] = null;
        }

        $oldValues = $product->toArray();
        $product->update($validated);

        AuditLog::create([
            'action'     => 'update',
            'model_type' => Product::class,
            'model_id'   => $product->id,
            'user_id'    => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => $product->toJson(),
        ]);

        return redirect()->route('products.show', $product)->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product from storage (soft delete).
     */
    public function destroy(Product $product, Request $request)
    {
        if (! \Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Invalid password. Deletion cancelled.');
        }

        $oldValues = $product->toArray();

        // Delete product image from storage
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        AuditLog::create([
            'action'     => 'delete',
            'model_type' => Product::class,
            'model_id'   => $product->id,
            'user_id'    => auth()->id(),
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

    public function showImport()
    {
        return view('products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
        ]);

        try {
            $import = new ProductsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $failures = $import->failures();

            AuditLog::create([
                'action'     => 'import',
                'model_type' => Product::class,
                'user_id'    => auth()->id(),
                'description' => "Imported {$imported} products" . ($failures->count() ? " ({$failures->count()} skipped)" : ''),
                'ip_address' => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            if ($failures->count() > 0) {
                $errors = $failures->map(fn($f) => "Row {$f->row()}: " . implode(', ', $f->errors()))->toArray();
                return back()
                    ->with('success', "{$imported} products imported.")
                    ->with('import_errors', $errors);
            }

            return redirect()->route('products.index')->with('success', "{$imported} products imported successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new ProductsExport(), 'products-' . date('Y-m-d') . '.xlsx');
    }

    public function storeVariant(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'value' => 'required|string|max:100',
            'sku' => 'required|string|unique:product_variants,sku|max:100',
            'quantity' => 'required|integer|min:0',
            'price_modifier' => 'nullable|numeric',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $product->variants()->create($validated);

        return back()->with('success', 'Variant added successfully.');
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'value' => 'required|string|max:100',
            'sku' => 'required|string|unique:product_variants,sku,' . $variant->id . '|max:100',
            'quantity' => 'required|integer|min:0',
            'price_modifier' => 'nullable|numeric',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $variant->update($validated);

        return back()->with('success', 'Variant updated successfully.');
    }

    public function destroyVariant(Product $product, ProductVariant $variant)
    {
        $variant->delete();
        return back()->with('success', 'Variant removed.');
    }
}
