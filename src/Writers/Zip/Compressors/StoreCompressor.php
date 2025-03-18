<?php

namespace PhpArchiveStream\Writers\Zip\Compressors;

use PhpArchiveStream\Writers\Zip\Compressors\Compressor;

class StoreCompressor implements Compressor
{
    public static function bitFlag(): int
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
