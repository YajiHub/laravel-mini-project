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
                'SKU'        => $p->sku,
                'Name'       => $p->name,
                'Category'   => $p->category->name ?? '-',
                'Price'      => $p->price,
                'Quantity'   => $p->quantity,
                'Unit'       => $p->unit,
                'Status'     => $p->quantity <= $p->low_stock_threshold ? 'Low Stock' : 'OK',
            ]);
    }

    public function headings(): array {
        return ['SKU', 'Name', 'Category', 'Price', 'Quantity', 'Unit', 'Status'];
    }

}
