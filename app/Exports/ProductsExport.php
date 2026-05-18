<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use App\Models\Product;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() {
        return Product::with('category', 'supplier')
            ->get()
            ->map(fn($p) => [
                'SKU'                 => $p->sku,
                'Name'                => $p->name,
                'Category'            => $p->category->name ?? '-',
                'Supplier'            => $p->supplier->name ?? '-',
                'Price'               => $p->price,
                'Cost'                => $p->cost,
                'Quantity'            => $p->quantity,
                'Low Stock Threshold' => $p->low_stock_threshold,
                'Unit'                => $p->unit,
                'Status'              => $p->quantity <= $p->low_stock_threshold ? 'Low Stock' : 'OK',
            ]);
    }

    public function headings(): array {
        return ['SKU', 'Name', 'Category', 'Supplier', 'Price', 'Cost', 'Quantity', 'Low Stock Threshold', 'Unit', 'Status'];
    }

}
