<?php
namespace CodeInSync\Core;

/**
 * Simple Registry class for storing and retrieving application-wide settings.
 * This is a basic implementation and can be extended as needed.
 */
class Registry
{
    protected static array $store = [];

    /**
     * Set a key-value pair in the registry.
     */
    public static function set(string $key, mixed $value): void
    {
        self::$store[$key] = $value;
    }

    /**
     * Get a value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$store[$key] ?? $default;
    }

    /**
     * Check if a key is registered.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$store);
    }

    /**
     * Push a value to an array entry.
     * Initializes the key as an array if not set.
     */
    public static function push(string $key, mixed $value): void
    {
        if (!isset(self::$store[$key]) || !is_array(self::$store[$key])) {
            self::$store[$key] = [];
        }
        self::$store[$key][] = $value;
    }

    /**
     * Get all registered items.
     */
    public static function all(): array
    {
        return self::$store;
    }
}