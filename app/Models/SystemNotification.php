<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemNotification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'product_id',
        'product_batch_id',
        'is_read',
        'read_at',
        'severity',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public static function getUnreadCount($userId = null): int
    {
        // Auto-mark welcome notifications older than 1 day as read
        self::where('type', 'welcome')
            ->where('is_read', false)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->where('created_at', '<=', now()->subDay())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return self::where('is_read', false)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->count();
    }

    public static function getRecentNotifications(int $limit = 15, $userId = null)
    {
        // First, auto-mark welcome notifications older than 1 day as read
        self::where('type', 'welcome')
            ->where('is_read', false)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->where('created_at', '<=', now()->subDay())
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return self::when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderByRaw("CASE 
                WHEN type = 'welcome' AND is_read = 0 AND created_at > ? THEN 0 
                ELSE 1 
                END ASC", [now()->subDay()])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public static function getUnreadNotifications(int $limit = 15, $userId = null)
    {
        return self::where('is_read', false)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderByRaw("CASE 
                WHEN type = 'welcome' AND created_at > ? THEN 0 
                ELSE 1 
                END ASC", [now()->subDay()])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
