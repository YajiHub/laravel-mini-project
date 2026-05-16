<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $cashierRole = \App\Models\Role::where('name', 'cashier')->first();
        $inventoryManagerRole = \App\Models\Role::where('name', 'inventory_manager')->first();
        $storeManagerRole = \App\Models\Role::where('name', 'store_manager')->first();
        $viewerRole = \App\Models\Role::where('name', 'viewer')->first();

        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@queensbuilders.com',
                'password' => bcrypt('password123'),
                'phone' => '09123456789',
                'is_active' => true,
                'role_id' => $adminRole->id ?? 1,
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@queensbuilders.com',
                'password' => bcrypt('password123'),
                'phone' => '09123456790',
                'is_active' => true,
                'role_id' => $cashierRole->id ?? 2,
            ],
            [
                'name' => 'Inventory Manager User',
                'email' => 'inventory@queensbuilders.com',
                'password' => bcrypt('password123'),
                'phone' => '09123456791',
                'is_active' => true,
                'role_id' => $inventoryManagerRole->id ?? 3,
            ],
            [
                'name' => 'Store Manager User',
                'email' => 'manager@queensbuilders.com',
                'password' => bcrypt('password123'),
                'phone' => '09123456792',
                'is_active' => true,
                'role_id' => $storeManagerRole->id ?? 4,
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@queensbuilders.com',
                'password' => bcrypt('password123'),
                'phone' => '09123456793',
                'is_active' => true,
                'role_id' => $viewerRole->id ?? 5,
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::firstOrCreate(['email' => $user['email']], $user);
        }
    }
}
