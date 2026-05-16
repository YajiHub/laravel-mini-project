<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call([
            RoleSeeder::class,
        ]);

        // Seed permissions
        $this->call([
            PermissionSeeder::class,
        ]);

        // Seed categories and suppliers
        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
        ]);

        // Seed users
        $this->call([
            UserSeeder::class,
        ]);

        // Seed products
        $this->call([
            ProductSeeder::class,
        ]);
    }
}
