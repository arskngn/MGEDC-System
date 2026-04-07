<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Service to manage permission cache invalidation for users
 */
class PermissionCacheService
{
    /**
     * Invalidate all permissions cache for a user
     */
    public static function invalidateUserPermissions(int $userId): void
    {
        // Clear all permission cache keys for this user by pattern
        Cache::forget("user_permission_{$userId}_*");
    }

    /**
     * Invalidate all permission caches (when role/permission definitions change)
     */
    public static function invalidateAll(): void
    {
        // In production, use cache tagging or queue a job to scan keys
        // For now, this is a placeholder for more sophisticated invalidation
        Cache::flush();
    }
}
