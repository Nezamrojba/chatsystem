<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Cache service for optimizing API responses
 * Reduces database queries and improves performance
 */
class CacheService
{
    /**
     * Cache TTL constants (in seconds)
     */
    private const CONVERSATIONS_TTL = 10; // 10 seconds
    private const MESSAGES_TTL = 5; // 5 seconds
    private const USER_TTL = 60; // 1 minute

    /**
     * Get cache key for user conversations
     */
    private static function conversationsKey(int $userId): string
    {
        return "conversations:user:{$userId}";
    }

    /**
     * Get cache key for conversation messages
     */
    private static function messagesKey(int $conversationId, int $page = 1): string
    {
        return "messages:conversation:{$conversationId}:page:{$page}";
    }

    /**
     * Get cache key for user data
     */
    private static function userKey(int $userId): string
    {
        return "user:{$userId}";
    }

    /**
     * Get cached conversations or fetch and cache
     */
    public static function getConversations(int $userId, callable $callback)
    {
        $key = self::conversationsKey($userId);
        
        return Cache::remember($key, self::CONVERSATIONS_TTL, $callback);
    }

    /**
     * Invalidate conversations cache for user
     */
    public static function invalidateConversations(int $userId): void
    {
        Cache::forget(self::conversationsKey($userId));
    }

    /**
     * Get cached messages or fetch and cache
     */
    public static function getMessages(int $conversationId, int $page, callable $callback)
    {
        $key = self::messagesKey($conversationId, $page);
        
        return Cache::remember($key, self::MESSAGES_TTL, $callback);
    }

    /**
     * Invalidate messages cache for conversation
     */
    public static function invalidateMessages(int $conversationId): void
    {
        // Invalidate all pages (simple approach)
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget(self::messagesKey($conversationId, $page));
        }
    }

    /**
     * Get cached user or fetch and cache
     */
    public static function getUser(int $userId, callable $callback)
    {
        $key = self::userKey($userId);
        
        return Cache::remember($key, self::USER_TTL, $callback);
    }

    /**
     * Invalidate user cache
     */
    public static function invalidateUser(int $userId): void
    {
        Cache::forget(self::userKey($userId));
    }

    /**
     * Clear all caches (for testing/debugging)
     */
    public static function clearAll(): void
    {
        Cache::flush();
    }
}

