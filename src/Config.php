<?php

namespace PhpArchiveStream;

class Config
{
    /**
     * All of the configuration items.
     */
    private array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Set the entire configuration array.
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Get a value from the configuration using dot notation.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->items;

        foreach ($keys as $keyPart) {
            if (! is_array($value) || ! array_key_exists($keyPart, $value)) {
                return $default;
            }
            $value = $value[$keyPart];
        }

        return $value;
    }

    /**
     * Set a value in the configuration using dot notation.
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$this->items;

        foreach ($keys as $keyPart) {
            if (! isset($current[$keyPart]) || ! is_array($current[$keyPart])) {
                $current[$keyPart] = [];
            }
            $current = &$current[$keyPart];
        }

        $current = $value;
    }

    /**
     * Get the entire configuration array.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Clear all of the configuration items.
     */
    public function clear(): void
    {
        $this->items = [];
    }
}
