<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing expenses table completely to rebuild it
        Schema::dropIfExists('expenses');

        // Create expenses table with correct structure
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_type_id')->constrained('expense_types')->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // No rollback
    }
};
