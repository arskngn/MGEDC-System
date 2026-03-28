<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Support\DefaultExpenseTypes;
use Illuminate\Console\Command;

class ResetExpenseTypes extends Command
{
    protected $signature = 'expense:reset';

    protected $description = 'Delete all expenses and reseed default expense types';

    public function handle(): int
    {
        $this->info('Deleting all expenses...');
        Expense::truncate();
        $this->info('✓ All expenses deleted.');

        $this->info('Reseeding default expense types...');
        DefaultExpenseTypes::syncToDatabase();
        $this->info('✓ Expense types reseeded successfully.');

        return self::SUCCESS;
    }
}
