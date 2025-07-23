<?php

namespace PhpArchiveStream;

use PhpArchiveStream\Support\StreamFactory;

class Config
{
    /**
     * Default configuration values.
     */
    protected const DEFAULTS = [
        'streamFactory' => StreamFactory::class,
        'zip' => [
            'enableZip64' => true,
            'input'       => ['chunkSize' => 1048576],
            'headers'     => [
                'Content-Type'              => 'application/zip',
                'Content-Disposition'       => 'attachment; filename="archive.zip"',
                'Content-Transfer-Encoding' => 'binary',
                'Pragma'                    => 'public',
                'Cache-Control'             => 'public, must-revalidate',
                'Connection'                => 'Keep-Alive',
            ],
        ],
        'tar' => [
            'input'   => ['chunkSize' => 1048576],
            'headers' => [
                'Content-Type'              => 'application/x-tar',
                'Content-Disposition'       => 'attachment; filename="archive.tar"',
                'Content-Transfer-Encoding' => 'binary',
                'Pragma'                    => 'public',
                'Cache-Control'             => 'public, must-revalidate',
                'Connection'                => 'Keep-Alive',
            ],
        ],
        'targz' => [
            'input'   => ['chunkSize' => 1048576],
            'headers' => [
                'Content-Type'              => 'application/x-tar',
                'Content-Disposition'       => 'attachment; filename="archive.tar.gz"',
                'Content-Transfer-Encoding' => 'binary',
                'Pragma'                    => 'public',
                'Cache-Control'             => 'public, must-revalidate',
                'Connection'                => 'Keep-Alive',
            ],
        ],
    ];

    /**
     * All of the configuration items.
     */
    private array $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $this->mergeConfigRecursive(static::DEFAULTS, $items);
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

    /**
     * Get the default configuration values.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return static::DEFAULTS;
    }

    /**
     * Recursively merge configuration arrays, properly overriding scalar values.
     */
    protected function mergeConfigRecursive(array $defaults, array $custom): array
    {
        $result = $defaults;

        foreach ($custom as $key => $value) {
            if (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                $result[$key] = $this->mergeConfigRecursive($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
