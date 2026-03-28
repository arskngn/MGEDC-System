<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Admin' => ['Banned', 'Dashboard', 'Request Report', 'Request Report Store', 'Download Attachment'],
            'Roles' => ['Roles Index', 'Roles Add', 'Roles Edit', 'Roles Save'],
            'Category' => ['All Categorys', 'Delete Category', 'Store Category', 'Import Category'],
            'Brand' => ['All Brands', 'Delete Brand', 'Store Brand', 'Import Brand'],
            'Unit' => ['All Unit', 'Delete Unit', 'Store Unit', 'Import Unit'],
            'Product' => ['All Product', 'Add Product', 'Product Edit', 'Product Store', 'Product Alert', 'Product Import', 'Product Searching', 'Download Product PDF', 'Download Product CSV'],
            'Warehouse' => ['All Warehouses', 'Delete Warehouse', 'Store Warehouse', 'Import Warehouses'],
            'Purchase' => ['All Purchases', 'New Purchase', 'Edit Purchase', 'Store Purchase', 'Download Purchase PDF', 'Purchase Update', 'Product Searching', 'Check Purchase Invoice', 'Download Purchase CSV', 'Download Purchase Invoice PDF'],
            'PurchaseReturn' => ['Item Of Purchase Return', 'All Purchase Return', 'Store Purchase Return', 'Edit Purchase Return', 'Update Purchase Return', 'Download Purchase Return PDF', 'Purchase Return Search Product', 'Purchase Return Check Invoice', 'Download Purchase Return CSV', 'Download Purchase Return Invoice PDF'],
            'Sale' => ['All Sales', 'Create Sale', 'Store Sale', 'Edit Sale', 'Update Sale', 'Download Sales PDF', 'Sale Search Product', 'Customer Searching', 'Last Sale Invoice', 'Download Sales CSV', 'Download Sale Invoice PDF'],
            'SaleReturn' => ['All Sales Return', 'Item Of Sale Return', 'Store Sale Return', 'Edit Sale Return', 'Update Sale Return', 'Download Sale Return PDF', 'Sale Return Search Product', 'Sale Return Search Customer', 'Download Sale Return CSV', 'Download Sale Return Invoice PDF'],
            'Adjustment' => ['All Adjustments', 'Create Adjustment', 'Store Adjustment', 'Download Adjustment Details PDF', 'Edit Adjustment', 'Update Adjustment', 'Adjustment Product Searching', 'Download Adjustment PDF', 'Download Adjustment CSV'],
            'Supplier' => ['All Suppliers', 'Store Supplier', 'Import Suppliers', 'Download Supplier PDF', 'Download Supplier CSV'],
            'Customer' => ['All Customers', 'Store Customer', 'Import Customers', 'Customer Notification Log', 'Single Customer Notification', 'Customer Notification Single', 'Customer All Notification', 'Customer Notification Send To All', 'Customer Email Details', 'Download Customer PDF', 'Download Customer CSV'],
            'SupplierPayment' => ['Supplier Payment Index', 'Supplier Payment Clear', 'Store Supplier Payment', 'Store Supplier Payment Receive', 'Download Supplier Payment PDF', 'Download Supplier Payment CSV'],
            'CustomerPayment' => ['Clear Payment Of Customer', 'All Customer Payments', 'Store Customer Payment', 'Store Payable Payment Of Customer', 'Download Customer Payment PDF', 'Download Customer Payment CSV'],
            'Transfer' => ['All Transfers', 'Create Transfer', 'Edit Transfer', 'Store Transfer', 'Download Transfer PDF', 'Update Transfer', 'Transfer Product Search', 'Download Transfer CSV', 'Download Transfer Details PDF'],
            'ExpenseType' => ['All Expense Types', 'Create Expense Type', 'Store Expense Type', 'Edit Expense Type', 'Update Expense Type', 'Delete Expense Type', 'Import Expense Types', 'Download Expense Type Template'],
            'Expense' => ['All Expenses', 'Store Expense', 'Import Expenses', 'Download Expense PDF', 'Download Expense CSV'],
            'GeneralSetting' => ['Setting Index', 'Update Setting', 'Setting System Configuration', 'Setting Logo Icon', 'Setting Logo Icon 2', 'Store Setting System Configuration', 'Store Setting Logo Icon'],
            'PaymentReport' => ['Supplier Payment Report', 'Customer Payment Report', 'Download Supplier Payment Report PDF', 'Download Supplier Payment Report CSV', 'Download Customer Payment Report PDF', 'Download Customer Payment Report CSV'],
            'DataEntryReport' => [
                'Product Data Entry Report', 'Customer Data Entry Report', 'Supplier Data Entry Report', 'Purchase Data Entry Report',
                'Purchase Return Data Entry Report', 'Sale Data Entry Report', 'Sale Return Data Entry Report', 'Report Data Entry Report Adjustment',
                'Transfer Data Entry Report', 'Expense Data Entry Report', 'Supplier Payment Data Entry Report', 'Customer Payment Data Entry Report',
            ],
            'Notification' => [
                'Global Notification Setting', 'Global Notification Setting Update', 'Notification Setting Templates', 'Notification Setting Template Edit',
                'Setting Notification Template Update', 'Email Notification Setting', 'Notification Setting Email Test', 'SMS Notification Setting',
                'Notification Setting SMS Test', 'Notification Setting Email Update', 'Notification Setting SMS Update',
            ],
            'System' => ['System Info', 'System Server Info', 'System Optimize', 'System Optimize Clear'],
            'Staff' => ['All Staffs', 'Save Staff', 'Staff Status', 'Staff Login'],
            'StockReport' => ['Stock Report', 'Stock Report PDF', 'Stock Report CSV'],
        ];

        foreach ($permissions as $module => $perms) {
            foreach ($permissions[$module] as $perm) {
                Permission::updateOrCreate([
                    'name' => $perm,
                    'module' => $module,
                ]);
            }
        }
    }
}
