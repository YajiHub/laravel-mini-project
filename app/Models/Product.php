<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'supplier_id', 'name', 'sku',
        'description', 'price', 'quantity',
        'low_stock_threshold', 'unit', 'image', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function posItems()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%")
                    ->orWhere('description', 'ilike', "%{$term}%");
    }

    // Methods
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function getTotalVariantQuantity(): int
    {
        return $this->variants->sum('quantity') + $this->quantity;
    }

    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity);
    }

    public function recordStockChange($quantity, $type, $notes = null, $userId = null)
    {
        return $this->stockTransactions()->create([
            'user_id' => $userId,
            'type' => $type, // 'in', 'out', 'adjustment'
            'quantity' => abs($quantity),
            'notes' => $notes,
            'reference_number' => 'TXN-' . now()->format('YmdHis'),
        ]);
    }
}