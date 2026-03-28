<?php

namespace App\Support;

use App\Models\Unit;

/**
 * Canonical default UoM rows (matches the Units UI reference). Synced on migrate and seed
 * so new clones and existing databases always get the full set, not only ltr/piece from sample inventory.
 */
final class DefaultUnits
{
    /**
     * @return list<array{name: string, short_name: string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'Bag', 'short_name' => 'bag'],
            ['name' => 'Liter', 'short_name' => 'ltr'],
            ['name' => 'Milliliter', 'short_name' => 'ml'],
            ['name' => 'Pack', 'short_name' => 'pack'],
            ['name' => 'Ton', 'short_name' => 'ton'],
            ['name' => 'Carton', 'short_name' => 'cartoon'],
            ['name' => 'Piece', 'short_name' => 'piece'],
            ['name' => 'Gram', 'short_name' => 'gram'],
            ['name' => 'Kilogram', 'short_name' => 'kg'],
            ['name' => 'Pieces', 'short_name' => 'pcs'],
        ];
    }

    public static function syncToDatabase(): void
    {
        foreach (self::definitions() as $u) {
            Unit::updateOrCreate(
                ['short_name' => $u['short_name']],
                ['name' => $u['name']]
            );
        }
    }
}
