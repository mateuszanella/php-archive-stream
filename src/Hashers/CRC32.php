<?php

namespace PhpArchiveStream\Hashers;

use HashContext;

class CRC32
{
    /**
     * The context for the CRC32 hash.
     *
     * @var HashContext
     */
    protected $context;

    /**
     * Initialize a new CRC32 hash context.
     */
    public function __construct()
    {
        $this->context = hash_init('crc32b');
    }

    /**
     * Initialize a new CRC32 hash context.
     */
    public static function init(): static
    {
        return new static;
    }

    /**
     * Update the CRC32 hash with the given data.
     *
     * @param  string  $data  The data to update the hash with.
     */
    public function update(string $data): void
    {
        hash_update($this->context, $data);
    }

    /**
     * Finalize the CRC32 hash and return the hash value.
     *
     * @return int The final CRC32 hash value.
     */
    public function finish(): int
    {
        return hexdec(hash_final($this->context));
    }
}
