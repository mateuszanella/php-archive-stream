<?php

namespace PhpArchiveStream\Compressors;

use PhpArchiveStream\Contracts\Compressor;
use PhpArchiveStream\Contracts\Zip\CompressionMethod;

class StoreCompressor implements CompressionMethod, Compressor
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
