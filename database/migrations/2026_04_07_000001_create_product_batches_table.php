<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('batch_number');
            $table->date('expiration_date');
            $table->date('manufactured_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('quantity_sold')->default(0);
            $table->integer('quantity_alert_sent')->default(0);
            $table->string('status')->default('active'); // active, expired, depleted
            $table->timestamps();
            
            $table->unique(['product_id', 'warehouse_id', 'batch_number']);
            $table->index('expiration_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
