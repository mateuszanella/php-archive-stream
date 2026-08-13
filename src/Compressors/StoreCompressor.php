<?php

namespace PhpArchiveStream\Compressors;

use PhpArchiveStream\Contracts\Compressor;

class StoreCompressor implements Compressor
{
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
