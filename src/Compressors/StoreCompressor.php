<?php

namespace PhpArchiveStream\Compressors;

use PhpArchiveStream\Compressors\Compressor;

class StoreCompressor implements Compressor
{
    public static function zipBitFlag(): int
    {
        return 0x00;
    }

    public static function init(): static
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
}
