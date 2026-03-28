<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->date('return_date')->nullable();
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('receivable_amount', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0);
        });

        if (Schema::hasColumn('purchase_returns', 'total_amount')) {
            DB::table('purchase_returns')->update([
                'receivable_amount' => DB::raw('COALESCE(total_amount, 0)'),
                'subtotal' => DB::raw('COALESCE(total_amount, 0)'),
            ]);
            Schema::table('purchase_returns', function (Blueprint $table) {
                $table->dropColumn('total_amount');
            });
        }

        foreach (DB::table('purchase_returns')->get() as $pr) {
            $p = DB::table('purchases')->where('id', $pr->purchase_id)->first();
            if ($p) {
                DB::table('purchase_returns')->where('id', $pr->id)->update([
                    'return_date' => $p->purchase_date ?? now()->toDateString(),
                    'warehouse_id' => $p->warehouse_id,
                ]);
            }
        }

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->constrained('purchase_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->decimal('purchase_quantity', 15, 4);
            $table->decimal('stock_quantity', 15, 4);
            $table->decimal('return_quantity', 15, 4);
            $table->string('unit_label', 50)->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();
        });

        Schema::create('purchase_return_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_payments');
        Schema::dropIfExists('purchase_return_items');

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn([
                'return_date',
                'warehouse_id',
                'subtotal',
                'discount',
                'receivable_amount',
                'received_amount',
            ]);
            $table->decimal('total_amount', 15, 2)->nullable();
        });
    }
};
