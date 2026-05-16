<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('products');

        // Search functionality with proper grouping
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query->orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        // Log the action
        AuditLog::create([
            'action' => 'create',
            'model_type' => Supplier::class,
            'model_id' => $supplier->id,
            'user_id' => auth()->id(),
            'old_values' => null,
            'new_values' => $supplier->toJson(),
        ]);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load('products');

        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'payment_terms' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldValues = $supplier->toArray();
        $supplier->update($validated);

        // Log the action
        AuditLog::create([
            'action' => 'update',
            'model_type' => Supplier::class,
            'model_id' => $supplier->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => $supplier->toJson(),
        ]);

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has products
        if ($supplier->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete supplier with products.');
        }

        $oldValues = $supplier->toArray();
        $supplier->delete();

        // Log the action
        AuditLog::create([
            'action' => 'delete',
            'model_type' => Supplier::class,
            'model_id' => $supplier->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => null,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
