<?php

namespace Database\Seeders;

use App\Support\DefaultUnits;
use Illuminate\Database\Seeder;

/**
 * Default units for fresh installs. Runs independently of products, categories, or brands
 * so UoM choices are always available after migrate/seed or clone.
 */
class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DefaultUnits::syncToDatabase();
    }
}
