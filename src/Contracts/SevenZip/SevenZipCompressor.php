<?php

namespace PhpArchiveStream\Contracts\SevenZip;

use PhpArchiveStream\Contracts\Compressor;

interface SevenZipCompressor extends Compressor
{
    /**
     * Get the 7z method ID for this compressor.
     *
     * @return int The method ID (e.g. 0x21 for LZMA2, 0x030101 for LZMA1).
     */
    public function getMethodId(): int;

    /**
     * Get the raw coder properties to store in the 7z folder metadata.
     *
     * @return string The raw property bytes.
     */
    public function getProperties(): string;
}
