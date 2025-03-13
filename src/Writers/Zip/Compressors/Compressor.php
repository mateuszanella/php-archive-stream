<?php

namespace PhpArchiveStream\Writers\Zip\Compressors;

interface Compressor
{
    public function compress(string $data): string;

    public static function bitFlag(): int;
}
