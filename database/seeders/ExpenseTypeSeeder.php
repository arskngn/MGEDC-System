<?php

namespace Database\Seeders;

use App\Support\DefaultExpenseTypes;
use Illuminate\Database\Seeder;

/**
 * Default expense types for fresh installs. Runs independently so expense type
 * choices are always available after migrate/seed or clone.
 */
class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        DefaultExpenseTypes::syncToDatabase();
    }
}
