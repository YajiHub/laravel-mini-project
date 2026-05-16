<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access, user management, audit logs',
                'is_active' => true,
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Cashier',
                'description' => 'Process sales transactions via POS system',
                'is_active' => true,
            ],
            [
                'name' => 'inventory_manager',
                'display_name' => 'Inventory Manager',
                'description' => 'Manage products, categories, suppliers, stock levels',
                'is_active' => true,
            ],
            [
                'name' => 'store_manager',
                'display_name' => 'Store Manager',
                'description' => 'View analytics, daily reports, sales trends',
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Viewer',
                'description' => 'Read-only access to reports and dashboards',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
