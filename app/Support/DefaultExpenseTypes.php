<?php

namespace App\Support;

use App\Models\ExpenseType;

/**
 * Default expense types for fresh installs. Runs independently so expense type choices
 * are always available after migrate/seed or clone.
 */
final class DefaultExpenseTypes
{
    /**
     * @return list<array{name: string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'Breakfast'],
            ['name' => 'Dinner'],
            ['name' => 'Lunch'],
            ['name' => 'Utility'],
        ];
    }

    public static function syncToDatabase(): void
    {
        foreach (self::definitions() as $type) {
            ExpenseType::updateOrCreate(
                ['name' => $type['name']],
                ['name' => $type['name']]
            );
        }
    }
}
