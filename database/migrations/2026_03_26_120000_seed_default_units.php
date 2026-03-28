<?php

use App\Support\DefaultUnits;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Ensures all default units exist after clone + migrate (even without running db:seed).
     */
    public function up(): void
    {
        DefaultUnits::syncToDatabase();
    }

    public function down(): void
    {
        // Intentionally empty: do not delete units that may be referenced by products.
    }
};
