<?php

namespace App\Services;

use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Store a new expense.
     */
    public function createExpense(array $data): Expense
    {
        return Expense::create([
            'expense_type_id' => $data['expense_type_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update an existing expense.
     */
    public function updateExpense(Expense $expense, array $data): Expense
    {
        $expense->update([
            'expense_type_id' => $data['expense_type_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return $expense;
    }

    /**
     * Delete an expense.
     */
    public function deleteExpense(Expense $expense): void
    {
        $expense->delete();
    }
}
