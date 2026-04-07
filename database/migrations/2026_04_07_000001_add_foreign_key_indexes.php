<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = function ($table, $column) {
            $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Column_name = ?", [$column]);
            return count($indexes) > 0;
        };

        // Add indexes on foreign key columns for better JOIN performance
        
        // Purchase table - supplier_id and warehouse_id
        if (!$indexExists('purchases', 'supplier_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index('supplier_id');
            });
        }

        if (!$indexExists('purchases', 'warehouse_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->index('warehouse_id');
            });
        }

        // Sale table - customer_id and warehouse_id
        if (!$indexExists('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('customer_id');
            });
        }

        if (!$indexExists('sales', 'warehouse_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('warehouse_id');
            });
        }

        // PurchaseReturn table - purchase_id and warehouse_id
        if (Schema::hasTable('purchase_returns')) {
            if (!$indexExists('purchase_returns', 'purchase_id')) {
                Schema::table('purchase_returns', function (Blueprint $table) {
                    $table->index('purchase_id');
                });
            }

            if (!$indexExists('purchase_returns', 'warehouse_id')) {
                Schema::table('purchase_returns', function (Blueprint $table) {
                    $table->index('warehouse_id');
                });
            }
        }

        // SaleReturn table - sale_id and warehouse_id
        if (Schema::hasTable('sale_returns')) {
            if (!$indexExists('sale_returns', 'sale_id')) {
                Schema::table('sale_returns', function (Blueprint $table) {
                    $table->index('sale_id');
                });
            }

            if (!$indexExists('sale_returns', 'warehouse_id')) {
                Schema::table('sale_returns', function (Blueprint $table) {
                    $table->index('warehouse_id');
                });
            }
        }
    }

    public function down(): void
    {
        $hasIndex = function ($table, $column) {
            $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Column_name = ?", [$column]);
            return count($indexes) > 0;
        };

        if ($hasIndex('purchases', 'supplier_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex(['supplier_id']);
            });
        }

        if ($hasIndex('purchases', 'warehouse_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropIndex(['warehouse_id']);
            });
        }

        if ($hasIndex('sales', 'customer_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex(['customer_id']);
            });
        }

        if ($hasIndex('sales', 'warehouse_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex(['warehouse_id']);
            });
        }

        if (Schema::hasTable('purchase_returns') && $hasIndex('purchase_returns', 'purchase_id')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->dropIndex(['purchase_id']);
            });
        }

        if (Schema::hasTable('purchase_returns') && $hasIndex('purchase_returns', 'warehouse_id')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->dropIndex(['warehouse_id']);
            });
        }

        if (Schema::hasTable('sale_returns') && $hasIndex('sale_returns', 'sale_id')) {
            Schema::table('sale_returns', function (Blueprint $table) {
                $table->dropIndex(['sale_id']);
            });
        }

        if (Schema::hasTable('sale_returns') && $hasIndex('sale_returns', 'warehouse_id')) {
            Schema::table('sale_returns', function (Blueprint $table) {
                $table->dropIndex(['warehouse_id']);
            });
        }
    }
};
