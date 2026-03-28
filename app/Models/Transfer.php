<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tracking_no
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property string|null $transfer_date
 * @property string|null $note
 * @property Warehouse $fromWarehouse
 * @property Warehouse $toWarehouse
 * @property \Illuminate\Database\Eloquent\Collection<TransferItem> $items
 */
class Transfer extends Model
{
    protected $fillable = [
        'tracking_no',
        'from_warehouse_id',
        'to_warehouse_id',
        'transfer_date',
        'note',
    ];

    protected $casts = [
        'transfer_date' => 'date',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferItem::class);
    }

    public static function nextTrackingNo(): string
    {
        $lastTransfer = static::orderByDesc('id')->first();
        $lastNumber = $lastTransfer ? (int) substr($lastTransfer->tracking_no, -6) : 0;
        return 'TRF' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
