<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTarget extends Model
{
    protected $fillable = [
        'user_id',
        'target_amount',
        'target_type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the status based on percentage.
     */
    public function getStatus(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'Achieved';
        } elseif ($percentage >= 50) {
            return 'On Track';
        }
        return 'Below Target';
    }

    /**
     * Get color class for UI.
     */
    public function getStatusColor(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'green';
        } elseif ($percentage >= 50) {
            return 'blue';
        }
        return 'red';
    }
}
