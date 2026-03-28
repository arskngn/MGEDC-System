<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Console\Command;

class SeedSampleExpenses extends Command
{
    protected $signature = 'expense:seed-sample';

    protected $description = 'Seed sample expenses for testing';

    public function handle(): int
    {
        $this->info('Seeding sample expenses...');

        $expenseTypes = ExpenseType::all();

        if ($expenseTypes->isEmpty()) {
            $this->error('No expense types found. Please run: php artisan expense:reset');
            return self::FAILURE;
        }

        $sampleExpenses = [
            [
                'expense_type_id' => $expenseTypes->firstWhere('name', 'Utility')?->id ?? $expenseTypes->first()->id,
                'date' => now()->subDays(10),
                'amount' => 500.00,
                'description' => 'Utility for Office !',
            ],
            [
                'expense_type_id' => $expenseTypes->firstWhere('name', 'Dinner')?->id ?? $expenseTypes->first()->id,
                'date' => now()->subDays(5),
                'amount' => 500.00,
                'description' => "It's dinner bill of month june!",
            ],
        ];

        foreach ($sampleExpenses as $expense) {
            if (Expense::where('amount', $expense['amount'])
                ->where('description', $expense['description'])
                ->exists()) {
                continue;
            }

            Expense::create($expense);
            $this->line("✓ Created expense: {$expense['description']}");
        }

        $this->info('✓ Sample expenses seeded successfully.');

        return self::SUCCESS;
    }
}
