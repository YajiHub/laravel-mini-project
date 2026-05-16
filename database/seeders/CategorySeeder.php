<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Steel Products',
                'description' => 'Rebars, steel bars, angles, and structural steel',
                'icon' => '⚙️',
                'is_active' => true,
            ],
            [
                'name' => 'Building Materials',
                'description' => 'Bricks, blocks, cement, sand, and aggregates',
                'icon' => '🧱',
                'is_active' => true,
            ],
            [
                'name' => 'Lumber & Wood',
                'description' => 'Plywood, boards, planks, and wood products',
                'icon' => '🪵',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical',
                'description' => 'Wiring, cables, switches, and electrical fixtures',
                'icon' => '⚡',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing',
                'description' => 'Pipes, fittings, valves, and plumbing fixtures',
                'icon' => '🚰',
                'is_active' => true,
            ],
            [
                'name' => 'Paint & Coatings',
                'description' => 'Paints, varnishes, stains, and protective coatings',
                'icon' => '🎨',
                'is_active' => true,
            ],
            [
                'name' => 'Hardware & Fasteners',
                'description' => 'Bolts, nuts, screws, nails, and hinges',
                'icon' => '🔩',
                'is_active' => true,
            ],
            [
                'name' => 'Tools & Equipment',
                'description' => 'Power tools, hand tools, and construction equipment',
                'icon' => '🔨',
                'is_active' => true,
            ],
            [
                'name' => 'Safety Equipment',
                'description' => 'Helmets, gloves, vests, and protective gear',
                'icon' => '🛡️',
                'is_active' => true,
            ],
            [
                'name' => 'Roofing Materials',
                'description' => 'Tiles, sheets, membranes, and roofing supplies',
                'icon' => '🏠',
                'is_active' => true,
            ],
            [
                'name' => 'Windows & Doors',
                'description' => 'Frames, glass, hardware, and door components',
                'icon' => '🪟',
                'is_active' => true,
            ],
            [
                'name' => 'Insulation & Soundproofing',
                'description' => 'Foam, fiberglass, and acoustic materials',
                'icon' => '🔇',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
