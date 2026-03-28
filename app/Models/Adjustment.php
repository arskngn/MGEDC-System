<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $tracking_no
 * @property int $warehouse_id
 * @property string|null $adjustment_date
 * @property string|null $note
 * @property Warehouse $warehouse
 * @property \Illuminate\Database\Eloquent\Collection<AdjustmentItem> $items
 */
class Adjustment extends Model
{
    protected $fillable = [
        'tracking_no',
        'warehouse_id',
        'adjustment_date',
        'note',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AdjustmentItem::class);
    }

    public static function nextTrackingNo(): string
    {
        $lastAdjustment = static::orderByDesc('id')->first();
        $lastNumber = $lastAdjustment ? (int) substr($lastAdjustment->tracking_no, -6) : 0;
        return 'ADJ' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
