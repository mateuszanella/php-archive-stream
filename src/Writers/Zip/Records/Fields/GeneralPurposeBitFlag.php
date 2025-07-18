<?php

namespace PhpArchiveStream\Writers\Zip\Records\Fields;

use PhpArchiveStream\Contracts\Compressor;

/**
 * A class representing the general purpose bit flag of a ZIP file.
 *
 * Each bit in the flag represents a different feature of the file:
 * - Bit 0: Indicates that the file is encrypted.
 * - Bit 1: Used for indicating compression values.
 * - Bit 2: Used for indicating compression values.
 * - Bit 3: Indicates the use of files descriptors for the CRC32, compressed size
 *  and uncompressed size fields in headers.
 * - Bit 4: Reserved for use for enhanced deflating.
 * - Bit 5: If this bit is set, this indicates that the file is compressed patched data.
 * - Bit 6: Strong encryption.
 * - Bit 7: Unused.
 * - Bit 8: Unused.
 * - Bit 9: Unused.
 * - Bit 10: Unused.
 * - Bit 11: Language encoding flag (EFS).
 * - Bit 12: Reserved.
 * - Bit 13: Indicates header encryption.
 * - Bit 14: Reserved.
 * - Bit 15: Reserved.
 */
class GeneralPurposeBitFlag
{
    /**
     * Bit flag for zero header.
     */
    public const ZERO_HEADER = 0b0000000000001000;

    /**
     * The value of the general purpose bit flag.
     */
    protected int $value = 0b0000000000000000;

    /**
     * Create a new instance of the GeneralPurposeBitFlag.
     */
    public static function create(): static
    {
        return new static;
    }

    /**
     * Set the zero header flag in the general purpose bit flag.
     */
    public function setZeroHeader(): static
    {
        $this->value |= static::ZERO_HEADER;

        return $this;
    }

    /**
     * Set the compression method in the general purpose bit flag.
     */
    public function setCompressionMethod(Compressor $compressor): static
    {
        $this->value |= $compressor::zipBitFlag();

        return $this;
    }

    /**
     * Get the value of the general purpose bit flag.
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
