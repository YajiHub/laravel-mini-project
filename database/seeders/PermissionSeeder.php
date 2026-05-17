<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Products
            ['name' => 'view_products', 'display_name' => 'View Products', 'module' => 'products'],
            ['name' => 'create_products', 'display_name' => 'Create Products', 'module' => 'products'],
            ['name' => 'edit_products', 'display_name' => 'Edit Products', 'module' => 'products'],
            ['name' => 'delete_products', 'display_name' => 'Delete Products', 'module' => 'products'],
            ['name' => 'import_products', 'display_name' => 'Import Products', 'module' => 'products'],
            ['name' => 'export_products', 'display_name' => 'Export Products', 'module' => 'products'],
            
            // Categories
            ['name' => 'view_categories', 'display_name' => 'View Categories', 'module' => 'categories'],
            ['name' => 'create_categories', 'display_name' => 'Create Categories', 'module' => 'categories'],
            ['name' => 'edit_categories', 'display_name' => 'Edit Categories', 'module' => 'categories'],
            ['name' => 'delete_categories', 'display_name' => 'Delete Categories', 'module' => 'categories'],
            
            // Suppliers
            ['name' => 'view_suppliers', 'display_name' => 'View Suppliers', 'module' => 'suppliers'],
            ['name' => 'create_suppliers', 'display_name' => 'Create Suppliers', 'module' => 'suppliers'],
            ['name' => 'edit_suppliers', 'display_name' => 'Edit Suppliers', 'module' => 'suppliers'],
            ['name' => 'delete_suppliers', 'display_name' => 'Delete Suppliers', 'module' => 'suppliers'],
            
            // Stock Transactions
            ['name' => 'view_stock_transactions', 'display_name' => 'View Stock Transactions', 'module' => 'stock'],
            ['name' => 'create_stock_transactions', 'display_name' => 'Create Stock Transactions', 'module' => 'stock'],
            ['name' => 'export_stock_transactions', 'display_name' => 'Export Stock Transactions', 'module' => 'stock'],
            
            // POS
            ['name' => 'access_pos', 'display_name' => 'Access POS System', 'module' => 'pos'],
            ['name' => 'process_pos_transaction', 'display_name' => 'Process POS Transactions', 'module' => 'pos'],
            ['name' => 'void_pos_transaction', 'display_name' => 'Void POS Transactions', 'module' => 'pos'],
            
            // Users
            ['name' => 'view_users', 'display_name' => 'View Users', 'module' => 'users'],
            ['name' => 'create_users', 'display_name' => 'Create Users', 'module' => 'users'],
            ['name' => 'edit_users', 'display_name' => 'Edit Users', 'module' => 'users'],
            ['name' => 'delete_users', 'display_name' => 'Delete Users', 'module' => 'users'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'module' => 'users'],
            ['name' => 'impersonate_user', 'display_name' => 'Impersonate Users', 'module' => 'users'],
            
            // Reports
            ['name' => 'view_reports', 'display_name' => 'View Reports', 'module' => 'reports'],
            ['name' => 'export_reports', 'display_name' => 'Export Reports', 'module' => 'reports'],
            ['name' => 'schedule_reports', 'display_name' => 'Schedule Reports', 'module' => 'reports'],
            
            // Audit & Logs
            ['name' => 'view_audit_logs', 'display_name' => 'View Audit Logs', 'module' => 'audit'],
            ['name' => 'export_audit_logs', 'display_name' => 'Export Audit Logs', 'module' => 'audit'],
            
            // Settings
            ['name' => 'view_settings', 'display_name' => 'View Settings', 'module' => 'settings'],
            ['name' => 'edit_settings', 'display_name' => 'Edit Settings', 'module' => 'settings'],
            ['name' => 'manage_backup', 'display_name' => 'Manage Backups', 'module' => 'settings'],
            ['name' => 'view_dashboard', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles()
    {
        $admin = \App\Models\Role::where('name', 'admin')->first();
        $cashier = \App\Models\Role::where('name', 'cashier')->first();
        $inventoryManager = \App\Models\Role::where('name', 'inventory_manager')->first();
        $storeManager = \App\Models\Role::where('name', 'store_manager')->first();

        // Admin - Full access
        if ($admin) {
            $allPermissions = \App\Models\Permission::all();
            $admin->permissions()->sync($allPermissions->pluck('id'));
        }

        // Cashier - POS only
        if ($cashier) {
            $cashierPermissions = \App\Models\Permission::whereIn('name', [
                'access_pos', 'process_pos_transaction', 'view_dashboard',
            ])->get();
            $cashier->permissions()->sync($cashierPermissions->pluck('id'));
        }

        // Inventory Manager - Full inventory control
        if ($inventoryManager) {
            $inventoryPermissions = \App\Models\Permission::whereIn('name', [
                'view_products', 'create_products', 'edit_products', 'delete_products',
                'view_categories', 'create_categories', 'edit_categories', 'delete_categories',
                'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
                'view_stock_transactions', 'create_stock_transactions', 'export_stock_transactions',
                'view_dashboard', 'export_products',
            ])->get();
            $inventoryManager->permissions()->sync($inventoryPermissions->pluck('id'));
        }

        // Store Manager - Reports and analytics
        if ($storeManager) {
            $managerPermissions = \App\Models\Permission::whereIn('name', [
                'view_products', 'view_categories', 'view_suppliers',
                'view_stock_transactions', 'view_reports', 'export_reports',
                'view_dashboard', 'view_audit_logs',
            ])->get();
            $storeManager->permissions()->sync($managerPermissions->pluck('id'));
        }
    }
}
