<?php

namespace App\Enums;

enum AdjustmentType: string
{
    case Addition = 'Add';
    case Subtraction = 'Remove';

    public function label(): string
    {
        return match($this) {
            self::Addition => 'Addition (+)',
            self::Subtraction => 'Subtraction (-)',
        };
    }
}
