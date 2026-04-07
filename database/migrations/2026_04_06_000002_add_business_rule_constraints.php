<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add CHECK constraints for business rules validation using raw SQL
        
        // Sales: paid_amount cannot exceed receivable_amount
        if (Schema::hasTable('sales')) {
            DB::statement('ALTER TABLE sales ADD CONSTRAINT check_sales_paid_amount CHECK (paid_amount <= receivable_amount)');
        }

        // Purchases: paid_amount cannot exceed payable_amount
        if (Schema::hasTable('purchases')) {
            DB::statement('ALTER TABLE purchases ADD CONSTRAINT check_purchases_paid_amount CHECK (paid_amount <= payable_amount)');
        }

        // Sale items: quantity must be positive
        if (Schema::hasTable('sale_items')) {
            DB::statement('ALTER TABLE sale_items ADD CONSTRAINT check_sale_items_quantity CHECK (quantity > 0)');
        }

        // Purchase items: quantity must be positive
        if (Schema::hasTable('purchase_items')) {
            DB::statement('ALTER TABLE purchase_items ADD CONSTRAINT check_purchase_items_quantity CHECK (quantity > 0)');
        }

        // Sale returns: returned items positive
        if (Schema::hasTable('sale_return_items')) {
            DB::statement('ALTER TABLE sale_return_items ADD CONSTRAINT check_sale_return_items_quantity CHECK (return_quantity > 0)');
        }

        // Purchase returns: returned items positive
        if (Schema::hasTable('purchase_return_items')) {
            DB::statement('ALTER TABLE purchase_return_items ADD CONSTRAINT check_purchase_return_items_quantity CHECK (return_quantity > 0)');
        }

        // Products: alert_quantity non-negative
        if (Schema::hasTable('products')) {
            DB::statement('ALTER TABLE products ADD CONSTRAINT check_products_alert_qty CHECK (alert_quantity >= 0)');
        }
    }

    public function down(): void
    {
        // Remove CHECK constraints
        $constraints = [
            'sales' => 'check_sales_paid_amount',
            'purchases' => 'check_purchases_paid_amount',
            'sale_items' => 'check_sale_items_quantity',
            'purchase_items' => 'check_purchase_items_quantity',
            'sale_return_items' => 'check_sale_return_items_quantity',
            'purchase_return_items' => 'check_purchase_return_items_quantity',
            'products' => 'check_products_alert_qty',
        ];

        foreach ($constraints as $table => $constraint) {
            if (Schema::hasTable($table)) {
                try {
                    DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraint}");
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
            }
        }
    }
};
