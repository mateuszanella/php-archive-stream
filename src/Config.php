<?php

namespace PhpArchiveStream;

class Config
{
    /**
     * All of the configuration items.
     */
    private static array $items = [];

    /**
     * Set the entire configuration array.
     */
    public static function setItems(array $items): void
    {
        static::$items = $items;
    }

    /**
     * Get a value from the configuration using dot notation.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = static::$items;

        foreach ($keys as $keyPart) {
            if (!is_array($value) || !array_key_exists($keyPart, $value)) {
                return $default;
            }
            $value = $value[$keyPart];
        }

        return $value;
    }

    /**
     * Set a value in the configuration using dot notation.
     */
    public static function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &static::$items;

        foreach ($keys as $keyPart) {
            if (!isset($current[$keyPart]) || !is_array($current[$keyPart])) {
                $current[$keyPart] = [];
            }
            $current = &$current[$keyPart];
        }

        $current = $value;
    }

    /**
     * Get the entire configuration array.
     */
    public static function all(): array
    {
        return static::$items;
    }
}
