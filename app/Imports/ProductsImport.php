<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private $imported = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        $category = Category::where('name', $row['category'])->first();
        $supplier = Supplier::where('name', $row['supplier'])->first();

        $existing = Product::where('sku', $row['sku'])->first();
        if ($existing) {
            $existing->update([
                'name' => $row['name'],
                'category_id' => $category?->id,
                'supplier_id' => $supplier?->id,
                'price' => $row['price'],
                'cost' => $row['cost'] ?? 0,
                'quantity' => $row['quantity'] ?? 0,
                'low_stock_threshold' => $row['low_stock_threshold'] ?? 10,
                'unit' => $row['unit'] ?? 'pcs',
            ]);

            $this->imported++;
            return null;
        }

        $this->imported++;

        return new Product([
            'sku' => $row['sku'],
            'name' => $row['name'],
            'category_id' => $category?->id,
            'supplier_id' => $supplier?->id,
            'price' => $row['price'],
            'cost' => $row['cost'] ?? 0,
            'quantity' => $row['quantity'] ?? 0,
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 10,
            'unit' => $row['unit'] ?? 'pcs',
            'is_active' => true,
        ]);
    }

    public function rules(): array
    {
        return [
            'sku' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'supplier' => 'required|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'numeric|min:0',
            'quantity' => 'integer|min:0',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedCount(): int
    {
        return count($this->failures());
    }
}
