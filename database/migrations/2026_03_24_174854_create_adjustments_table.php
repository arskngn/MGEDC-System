<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_no')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->date('adjustment_date');
            $table->text('note')->nullable();
            $table->timestamps();
            
            $table->index(['warehouse_id', 'adjustment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustments');
    }
};
