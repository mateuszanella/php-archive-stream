<?php

namespace PhpArchiveStream\Writers\Zip\Compressors;

use PhpArchiveStream\Writers\Zip\Compressors\Compressor;

class StoreCompressor implements Compressor
{
    public function compress(string $data): string
    {
        return $data;
    }

    public static function bitFlag(): int
    {
        return 0x0;
    }
}
