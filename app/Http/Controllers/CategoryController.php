<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        // Log the action
        AuditLog::create([
            'action' => 'create',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'user_id' => auth()->id(),
            'old_values' => null,
            'new_values' => $category->toJson(),
        ]);

        return redirect()->route('categories.show', $category)->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $category->load('products');

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $oldValues = $category->toArray();
        $category->update($validated);

        // Log the action
        AuditLog::create([
            'action' => 'update',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => $category->toJson(),
        ]);

        return redirect()->route('categories.show', $category)->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with products.');
        }

        $oldValues = $category->toArray();
        $category->delete();

        // Log the action
        AuditLog::create([
            'action' => 'delete',
            'model_type' => Category::class,
            'model_id' => $category->id,
            'user_id' => auth()->id(),
            'old_values' => json_encode($oldValues),
            'new_values' => null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
