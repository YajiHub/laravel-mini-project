<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, WithUpserts, WithBatchInserts, SkipsOnFailure
{
    use SkipsFailures;

    private $imported = 0;

    public function model(array $row)
    {
        $category = Category::where('name', $row['category'])->first();
        $supplier = Supplier::where('name', $row['supplier'])->first();

        $this->imported++;

        $product = new Product([
            'sku'                => $row['sku'],
            'name'               => $row['name'],
            'category_id'        => $category?->id,
            'supplier_id'        => $supplier?->id,
            'price'              => $row['price'],
            'cost'               => $row['cost'] ?? 0,
            'quantity'           => $row['quantity'] ?? 0,
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 10,
            'unit'               => $row['unit'] ?? 'pcs',
            'is_active'          => true,
        ]);

        $product->deleted_at = null;

        return $product;
    }

    public function uniqueBy()
    {
        return 'sku';
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function rules(): array
    {
        return [
            'sku'      => 'required|string|max:100',
            'name'     => 'required|string|max:255',
            'category'  => 'required|string',
            'supplier'  => 'required|string',
            'price'     => 'required|numeric|min:0',
            'cost'      => 'numeric|min:0',
            'quantity'  => 'integer|min:0',
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
