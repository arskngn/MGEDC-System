<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UnitSeeder::class,
            InventorySeeder::class,
            ExpenseTypeSeeder::class,
        ]);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'status' => 1,
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);
            // Give admin all permissions
            $adminRole->permissions()->sync(Permission::all());
        }

        // Create Staff User
        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
                'status' => 1,
            ]
        );

        $staffRole = Role::where('name', 'staff')->first();
        if ($staffRole) {
            $staff->roles()->syncWithoutDetaching([$staffRole->id]);

            // Set permissions from images
            $staffPermissions = [
                'Dashboard',
                'All Product', 'Add Product', 'Product Edit', 'Product Store', 'Product Alert', 'Product Import', 'Product Searching', 'Download Product PDF', 'Download Product CSV',
                'All Categorys', 'Delete Category', 'Store Category', 'Import Category',
                'All Brands', 'Delete Brand', 'Store Brand', 'Import Brand',
                'All Unit', 'Delete Unit', 'Store Unit', 'Import Unit',
                'All Warehouses', 'Delete Warehouse', 'Store Warehouse', 'Import Warehouses',
                'All Purchases', 'New Purchase', 'Edit Purchase', 'Store Purchase', 'Download Purchase PDF', 'Purchase Update', 'Product Searching', 'Check Purchase Invoice', 'Download Purchase CSV', 'Download Purchase Invoice PDF', 'Store Supplier Payment',
                'Item Of Purchase Return', 'All Purchase Return', 'Store Purchase Return', 'Edit Purchase Return', 'Update Purchase Return', 'Download Purchase Return PDF', 'Purchase Return Check Invoice', 'Download Purchase Return CSV', 'Download Purchase Return Invoice PDF',
                'All Sales', 'Create Sale', 'Store Sale', 'Edit Sale', 'Update Sale', 'Download Sales PDF', 'Sale Search Product', 'Customer Searching', 'Last Sale Invoice', 'Download Sales CSV', 'Download Sale Invoice PDF', 'Store Customer Payment',
                'All Sales Return', 'Item Of Sale Return', 'Store Sale Return', 'Edit Sale Return', 'Update Sale Return', 'Download Sale Return PDF', 'Sale Return Search Product', 'Sale Return Search Customer', 'Download Sale Return CSV', 'Download Sale Return Invoice PDF',
                'All Adjustments', 'Create Adjustment', 'Store Adjustment', 'Download Adjustment Details PDF', 'Edit Adjustment', 'Update Adjustment', 'Adjustment Product Searching', 'Download Adjustment PDF', 'Download Adjustment CSV',
                'All Suppliers', 'Store Supplier', 'Import Suppliers', 'Download Supplier PDF', 'Download Supplier CSV',
                'All Customers', 'Store Customer', 'Import Customers', 'Customer Notification Log', 'Single Customer Notification', 'Customer Notification Single', 'Customer All Notification', 'Customer Notification Send To All', 'Customer Email Details', 'Download Customer PDF', 'Download Customer CSV',
                'All Transfers', 'Create Transfer', 'Edit Transfer', 'Store Transfer', 'Download Transfer PDF', 'Update Transfer', 'Transfer Product Search', 'Download Transfer CSV', 'Download Transfer Details PDF',
                'All Expenses', 'Store Expense', 'Import Expenses', 'Download Expense PDF', 'Download Expense CSV',
                'Supplier Payment Report', 'Customer Payment Report',
                'Supplier Payment Index', 'Supplier Payment Clear', 'Store Supplier Payment', 'Store Supplier Payment Receive', 'Download Supplier Payment PDF', 'Download Supplier Payment CSV',
                'All Customer Payments', 'Clear Payment Of Customer', 'Store Customer Payment', 'Store Payable Payment Of Customer', 'Download Customer Payment PDF', 'Download Customer Payment CSV',
                'Stock Report', 'Stock Report PDF', 'Stock Report CSV',
                'Setting Index', 'Update Setting',
            ];

            $permissionIds = Permission::whereIn('name', $staffPermissions)->pluck('id');
            $staffRole->permissions()->sync($permissionIds);
        }
    }
}
