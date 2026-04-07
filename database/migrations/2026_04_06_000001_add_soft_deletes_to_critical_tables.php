<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add soft deletes to critical financial tables
        if (Schema::hasTable('sales') && !Schema::hasColumn('sales', 'deleted_at')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('purchases') && !Schema::hasColumn('purchases', 'deleted_at')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'deleted_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'deleted_at')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('sale_returns') && !Schema::hasColumn('sale_returns', 'deleted_at')) {
            Schema::table('sale_returns', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('purchase_returns') && !Schema::hasColumn('purchase_returns', 'deleted_at')) {
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Remove soft deletes
        $tables = ['sales', 'purchases', 'customers', 'suppliers', 'sale_returns', 'purchase_returns'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
