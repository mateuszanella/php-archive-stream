<?php

declare(strict_types=1);

namespace PhpArchiveStream\Contracts\Zip;

use PhpArchiveStream\Contracts\Compressor;

interface ZipCompressor extends Compressor
{
    /**
     * Get the ZIP compression method code (APPNOTE 4.4.5).
     *
     * @return int The compression method value (e.g. 0x00 for store, 0x08 for deflate).
     */
    public function getCompressionMethod(): int;
}
