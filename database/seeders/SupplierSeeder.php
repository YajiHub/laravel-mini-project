<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PhilCement Corporation',
                'contact_person' => 'Juan Dela Cruz',
                'email' => 'sales@philcement.com.ph',
                'phone' => '02-8123-4567',
                'address' => '123 Industrial Avenue, Manila',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'postal_code' => '1000',
                'payment_terms' => 'NET 30',
                'notes' => 'Primary cement supplier',
                'is_active' => true,
            ],
            [
                'name' => 'Steel Dynamics Inc.',
                'contact_person' => 'Maria Santos',
                'email' => 'orders@steeldynamics.com.ph',
                'phone' => '02-8456-7890',
                'address' => '456 Industrial Park, Cebu',
                'city' => 'Cebu City',
                'province' => 'Cebu',
                'postal_code' => '6000',
                'payment_terms' => 'NET 45',
                'notes' => 'Rebar and structural steel',
                'is_active' => true,
            ],
            [
                'name' => 'National Lumber Corp',
                'contact_person' => 'Pedro Reyes',
                'email' => 'info@nationallumber.ph',
                'phone' => '02-8789-0123',
                'address' => '789 Wood Processing, Laguna',
                'city' => 'Sta. Cruz',
                'province' => 'Laguna',
                'postal_code' => '4009',
                'payment_terms' => 'NET 14',
                'notes' => 'Lumber and wood products',
                'is_active' => true,
            ],
            [
                'name' => 'Electrical Solutions PH',
                'contact_person' => 'Lisa Wong',
                'email' => 'sales@electrosolutions.com.ph',
                'phone' => '02-8234-5678',
                'address' => '321 Makati Avenue, Makati',
                'city' => 'Makati City',
                'province' => 'Metro Manila',
                'postal_code' => '1200',
                'payment_terms' => 'NET 30',
                'notes' => 'Electrical supplies and fixtures',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing Experts Ltd',
                'contact_person' => 'Roberto Carlos',
                'email' => 'info@plumbingexperts.ph',
                'phone' => '02-8567-8901',
                'address' => '654 QC Avenue, Quezon City',
                'city' => 'Quezon City',
                'province' => 'Metro Manila',
                'postal_code' => '1100',
                'payment_terms' => 'NET 30',
                'notes' => 'Plumbing pipes and fittings',
                'is_active' => true,
            ],
            [
                'name' => 'ProPaint Industries',
                'contact_person' => 'Angela Mercado',
                'email' => 'orders@propaint.com.ph',
                'phone' => '02-8890-1234',
                'address' => '987 Industrial Road, Valenzuela',
                'city' => 'Valenzuela',
                'province' => 'Bulacan',
                'postal_code' => '1440',
                'payment_terms' => 'NET 30',
                'notes' => 'Paint, varnish, and coatings',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            \App\Models\Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
