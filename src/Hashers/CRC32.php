<?php

namespace PhpArchiveStream\Hashers;

class CRC32
{
    protected $context;

    public function __construct()
    {
        $this->context = hash_init('crc32b');
    }

    public static function init(): static
    {
        return new static;
    }

    public function update(string $data): void
    {
        hash_update($this->context, $data);
    }

    public function finish(): string
    {
        return hash_final($this->context, true);
    }
}
