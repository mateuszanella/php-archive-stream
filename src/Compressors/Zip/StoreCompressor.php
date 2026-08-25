<?php

declare(strict_types=1);

namespace PhpArchiveStream\Compressors\Zip;

use PhpArchiveStream\Contracts\Zip\ZipCompressor;

/**
 * @internal
 *
 * @phpstan-consistent-constructor
 */
class StoreCompressor implements ZipCompressor
{
    public static function init(array $options = []): static
    {
        return new static;
    }

    public function compress(string $data): string
    {
        return $data;
    }

    public function finish(): string
    {
        return '';
    }

    public function getCompressionMethod(): int
    {
        return 0x00;
    }
}
