<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }
}
