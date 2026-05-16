<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steelCategory = \App\Models\Category::where('name', 'Steel Products')->first();
        $buildingMaterials = \App\Models\Category::where('name', 'Building Materials')->first();
        $lumberCategory = \App\Models\Category::where('name', 'Lumber & Wood')->first();
        $electricalCategory = \App\Models\Category::where('name', 'Electrical')->first();

        $steelSupplier = \App\Models\Supplier::first();

        // Sample Rebar product with variants
        $rebar = \App\Models\Product::create([
            'category_id' => $steelCategory->id,
            'supplier_id' => $steelSupplier->id,
            'name' => 'Deformed Steel Rebar',
            'sku' => 'REBAR-MS',
            'description' => 'High-quality deformed steel rebar Fe500 for construction',
            'price' => 50.00,
            'quantity' => 100,
            'low_stock_threshold' => 20,
            'unit' => 'bundle',
            'is_active' => true,
        ]);

        // Create rebar variants (different sizes)
        $rebarSizes = ['6mm', '8mm', '10mm', '12mm', '16mm', '20mm'];
        foreach ($rebarSizes as $index => $size) {
            \App\Models\ProductVariant::create([
                'product_id' => $rebar->id,
                'name' => $size,
                'value' => $size,
                'type' => 'size',
                'quantity' => 100 + ($index * 10),
                'low_stock_threshold' => 15,
                'sku' => 'REBAR-' . $size,
                'price_modifier' => $index * 2,
                'is_active' => true,
            ]);
        }

        // Cement product
        $cement = \App\Models\Product::create([
            'category_id' => $buildingMaterials->id,
            'supplier_id' => $steelSupplier->id,
            'name' => 'Portland Cement Type I',
            'sku' => 'CEMENT-PI',
            'description' => 'Standard Portland cement for general construction',
            'price' => 280.00,
            'quantity' => 500,
            'low_stock_threshold' => 100,
            'unit' => 'bag',
            'is_active' => true,
        ]);

        // Plywood product
        $plywood = \App\Models\Product::create([
            'category_id' => $lumberCategory->id,
            'supplier_id' => $steelSupplier->id,
            'name' => 'Construction Plywood',
            'sku' => 'PLYWOOD-4X8',
            'description' => '4x8 feet construction grade plywood',
            'price' => 1200.00,
            'quantity' => 50,
            'low_stock_threshold' => 10,
            'unit' => 'sheet',
            'is_active' => true,
        ]);

        // Plywood variants (different thicknesses)
        $plywoodThickness = ['6mm', '9mm', '12mm', '18mm'];
        foreach ($plywoodThickness as $thickness) {
            \App\Models\ProductVariant::create([
                'product_id' => $plywood->id,
                'name' => $thickness,
                'value' => $thickness,
                'type' => 'thickness',
                'quantity' => 50,
                'low_stock_threshold' => 8,
                'sku' => 'PLYWOOD-4X8-' . $thickness,
                'price_modifier' => ($thickness == '6mm' ? 0 : 200),
                'is_active' => true,
            ]);
        }

        // Electrical Wire
        $wire = \App\Models\Product::create([
            'category_id' => $electricalCategory->id,
            'supplier_id' => $steelSupplier->id,
            'name' => 'Electrical Copper Wire',
            'sku' => 'WIRE-CU',
            'description' => 'AWG electrical copper wire for building wiring',
            'price' => 450.00,
            'quantity' => 200,
            'low_stock_threshold' => 30,
            'unit' => 'roll',
            'is_active' => true,
        ]);

        // Wire variants
        $wireGauges = ['14 AWG', '12 AWG', '10 AWG', '8 AWG'];
        foreach ($wireGauges as $gauge) {
            \App\Models\ProductVariant::create([
                'product_id' => $wire->id,
                'name' => $gauge,
                'value' => $gauge,
                'type' => 'gauge',
                'quantity' => 100,
                'low_stock_threshold' => 20,
                'sku' => 'WIRE-CU-' . str_replace(' ', '', $gauge),
                'price_modifier' => 50,
                'is_active' => true,
            ]);
        }

        // Additional products
        $products = [
            [
                'category' => 'Building Materials',
                'name' => 'Red Bricks',
                'sku' => 'BRICKS-RED',
                'description' => 'Standard red clay bricks for construction',
                'price' => 8.00,
                'quantity' => 5000,
                'low_stock_threshold' => 500,
                'unit' => 'pcs',
            ],
            [
                'category' => 'Building Materials',
                'name' => 'Sand (Silica)',
                'sku' => 'SAND-SILICA',
                'description' => 'Fine silica sand for concrete and masonry',
                'price' => 400.00,
                'quantity' => 300,
                'low_stock_threshold' => 50,
                'unit' => 'bag',
            ],
        ];

        foreach ($products as $productData) {
            $category = \App\Models\Category::where('name', $productData['category'])->first();
            \App\Models\Product::create([
                'category_id' => $category->id,
                'supplier_id' => $steelSupplier->id,
                'name' => $productData['name'],
                'sku' => $productData['sku'],
                'description' => $productData['description'],
                'price' => $productData['price'],
                'quantity' => $productData['quantity'],
                'low_stock_threshold' => $productData['low_stock_threshold'],
                'unit' => $productData['unit'],
                'is_active' => true,
            ]);
        }
    }
}
