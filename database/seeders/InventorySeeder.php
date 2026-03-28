<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Support\DefaultUnits;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DefaultUnits::syncToDatabase();

        $ltr = Unit::where('short_name', 'ltr')->firstOrFail();
        $piece = Unit::where('short_name', 'piece')->firstOrFail();

        $catElectronics = Category::firstOrCreate(['name' => 'Electronics']);
        $catGrocery = Category::firstOrCreate(['name' => 'Grocery']);

        $brandPanasonic = Brand::firstOrCreate(['name' => 'Panasonic']);
        $brandXyz = Brand::firstOrCreate(['name' => 'Xyz']);

        $w1 = Warehouse::firstOrCreate(
            ['name' => 'Warehouse'],
            ['address' => '123 Sample Street, Metro Manila', 'status' => true]
        );
        Warehouse::firstOrCreate(
            ['name' => 'Warehouse One'],
            ['address' => 'Block A, Industrial Area', 'status' => true]
        );

        $supplier = Supplier::firstOrCreate(
            ['email' => 'zurobyna@test.com'],
            [
                'name' => 'Audrey Collier',
                'phone' => '995645132625',
            ]
        );

        $p1 = Product::updateOrCreate(
            ['sku' => 'GSO001'],
            [
                'name' => '2L Sunflower Oil',
                'unit_id' => $ltr->id,
                'category_id' => $catGrocery->id,
                'brand_id' => $brandXyz->id,
                'default_purchase_price' => 250,
                'alert_quantity' => 500,
                'note' => null,
            ]
        );
        $p2 = Product::updateOrCreate(
            ['sku' => 'ER240'],
            [
                'name' => 'Panasonic Trimmer ER240',
                'unit_id' => $piece->id,
                'category_id' => $catElectronics->id,
                'brand_id' => $brandPanasonic->id,
                'default_purchase_price' => 500,
                'alert_quantity' => 10,
                'note' => null,
            ]
        );

        if (Purchase::where('invoice_no', 'P-0000200')->exists()) {
            return;
        }

        $purchase = Purchase::create([
            'invoice_no' => 'P-0000200',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $w1->id,
            'purchase_date' => '2024-07-10',
            'note' => 'Purchase from Audrey Collier!',
            'subtotal' => 50000,
            'discount' => 0,
            'payable_amount' => 50000,
            'paid_amount' => 30000,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'sku' => $p1->sku,
            'quantity' => 100,
            'unit_label' => 'ltr',
            'unit_price' => 250,
            'line_total' => 25000,
        ]);
        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'sku' => $p2->sku,
            'quantity' => 50,
            'unit_label' => 'piece',
            'unit_price' => 500,
            'line_total' => 25000,
        ]);

        PurchasePayment::create([
            'purchase_id' => $purchase->id,
            'user_id' => null,
            'amount' => 30000,
        ]);

        $purchaseReturn = PurchaseReturn::create([
            'purchase_id' => $purchase->id,
            'return_invoice_no' => 'PR-0000001',
            'return_date' => $purchase->purchase_date,
            'warehouse_id' => $purchase->warehouse_id,
            'subtotal' => 5000,
            'discount' => 0,
            'receivable_amount' => 5000,
            'received_amount' => 0,
            'note' => 'Sample return record for linking to purchase return module.',
        ]);

        $oilLine = $purchase->items->firstWhere('product_id', $p1->id);
        if ($oilLine) {
            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'purchase_item_id' => $oilLine->id,
                'product_id' => $p1->id,
                'product_name' => $p1->name,
                'sku' => $p1->sku,
                'purchase_quantity' => (float) $oilLine->quantity,
                'stock_quantity' => 100,
                'return_quantity' => 20,
                'unit_label' => 'ltr',
                'unit_price' => 250,
                'line_total' => 5000,
            ]);
        }

        // Create sample customers for sales
        $customer1 = Customer::firstOrCreate(
            ['email' => 'amir.hensley@example.com'],
            [
                'name' => 'Amir Hensley',
                'phone' => '1234567890',
                'address' => '123 Sample Street, Metro Manila',
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['email' => 'ruth.good@example.com'],
            [
                'name' => 'Ruth Good',
                'phone' => '0987654321',
                'address' => '456 Shopping Center, QC',
            ]
        );

        // Create sample sales with unpaid balance to show "Receive Payment" option
        if (!Sale::where('invoice_no', 'S-000017')->exists()) {
            $sale1 = Sale::create([
                'invoice_no' => 'S-000017',
                'customer_id' => $customer1->id,
                'warehouse_id' => $w1->id,
                'sale_date' => now()->format('Y-m-d'),
                'note' => 'Sample sale with unpaid balance',
                'subtotal' => 50000,
                'discount' => 0,
                'receivable_amount' => 50000,
                'paid_amount' => 20000,
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $p1->id,
                'product_name' => $p1->name,
                'sku' => $p1->sku,
                'quantity' => 100,
                'unit_label' => 'ltr',
                'unit_price' => 250,
                'line_total' => 25000,
            ]);

            SaleItem::create([
                'sale_id' => $sale1->id,
                'product_id' => $p2->id,
                'product_name' => $p2->name,
                'sku' => $p2->sku,
                'quantity' => 50,
                'unit_label' => 'piece',
                'unit_price' => 500,
                'line_total' => 25000,
            ]);
        }

        if (!Sale::where('invoice_no', 'S-000018')->exists()) {
            $sale2 = Sale::create([
                'invoice_no' => 'S-000018',
                'customer_id' => $customer2->id,
                'warehouse_id' => $w1->id,
                'sale_date' => now()->subDay()->format('Y-m-d'),
                'note' => 'Another sample sale with unpaid balance',
                'subtotal' => 75000,
                'discount' => 5000,
                'receivable_amount' => 70000,
                'paid_amount' => 30000,
            ]);

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $p1->id,
                'product_name' => $p1->name,
                'sku' => $p1->sku,
                'quantity' => 150,
                'unit_label' => 'ltr',
                'unit_price' => 250,
                'line_total' => 37500,
            ]);

            SaleItem::create([
                'sale_id' => $sale2->id,
                'product_id' => $p2->id,
                'product_name' => $p2->name,
                'sku' => $p2->sku,
                'quantity' => 75,
                'unit_label' => 'piece',
                'unit_price' => 500,
                'line_total' => 37500,
            ]);
        }

        // Create sample sales returns
        if (!Sale::where('invoice_no', 'S-000015')->exists()) {
            $sale3 = Sale::create([
                'invoice_no' => 'S-000015',
                'customer_id' => $customer1->id,
                'warehouse_id' => $w1->id,
                'sale_date' => now()->subDays(5)->format('Y-m-d'),
                'note' => 'Sample sale for return',
                'subtotal' => 14000000,
                'discount' => 0,
                'receivable_amount' => 14000000,
                'paid_amount' => 0,
            ]);

            SaleItem::create([
                'sale_id' => $sale3->id,
                'product_id' => $p1->id,
                'product_name' => $p1->name,
                'sku' => $p1->sku,
                'quantity' => 4000,
                'unit_label' => 'piece',
                'unit_price' => 4000,
                'line_total' => 14000000,
            ]);

            // Create return for sale 3
            $saleReturn1 = \App\Models\SaleReturn::create([
                'sale_id' => $sale3->id,
                'return_invoice_no' => 'SR-000015',
                'return_date' => now()->subDays(3)->format('Y-m-d'),
                'warehouse_id' => $w1->id,
                'subtotal' => 14000000,
                'discount' => 0,
                'payable_amount' => 14000000,
                'paid_amount' => 0,
                'note' => 'Sample return record',
            ]);

            \App\Models\SaleReturnItem::create([
                'sale_return_id' => $saleReturn1->id,
                'sale_item_id' => $sale3->items->first()->id,
                'product_id' => $p1->id,
                'product_name' => $p1->name,
                'sku' => $p1->sku,
                'sale_quantity' => 4000,
                'return_quantity' => 1000,
                'unit_label' => 'piece',
                'unit_price' => 4000,
                'line_total' => 4000000,
            ]);
        }
    }
}
