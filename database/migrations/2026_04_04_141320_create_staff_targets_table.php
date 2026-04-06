<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('target_amount', 15, 2);
            $table->enum('target_type', ['daily', 'weekly', 'monthly']);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            
            // Ensure a user doesn't have overlapping targets of the same type (simple version)
            // For a more robust system, we'd add complex validation in the controller
            $table->index(['user_id', 'target_type', 'start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_targets');
    }
};
