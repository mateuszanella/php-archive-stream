<?php

namespace PhpArchiveStream\Contracts;

interface Coder
{
    /**
     * Get the 7z method ID for this coder.
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
