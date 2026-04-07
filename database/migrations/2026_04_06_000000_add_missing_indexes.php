<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Helper function to check if index exists
        $indexExists = function ($table, $column) {
            $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Column_name = ?", [$column]);
            return count($indexes) > 0;
        };

        // Add indexes only if they don't exist
        
        // Sales table indexes
        if (!$indexExists('sales', 'invoice_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index('invoice_no');
            });
        }

        // Products table indexes
        if (!$indexExists('products', 'sku')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('sku');
            });
        }

        // Customers table indexes
        if (Schema::hasTable('customers') && !$indexExists('customers', 'phone')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('phone');
            });
        }

        // Suppliers table indexes
        if (Schema::hasTable('suppliers') && !$indexExists('suppliers', 'phone')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->index('phone');
            });
        }
    }

    public function down(): void
    {
        // Remove added indexes
        $hasIndex = function ($table, $column) {
            $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Column_name = ?", [$column]);
            return count($indexes) > 0;
        };

        if ($hasIndex('sales', 'invoice_no')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropIndex(['invoice_no']);
            });
        }

        if ($hasIndex('products', 'sku')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['sku']);
            });
        }

        if (Schema::hasTable('customers') && $hasIndex('customers', 'phone')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['phone']);
            });
        }

        if (Schema::hasTable('suppliers') && $hasIndex('suppliers', 'phone')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropIndex(['phone']);
            });
        }
    }
};
