<?php

namespace App\Events;

use App\Models\ProductBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductBatchExpiringAlert
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ProductBatch $batch,
        public int $daysUntilExpiration,
        public string $severity = 'warning'
    ) {}
}
