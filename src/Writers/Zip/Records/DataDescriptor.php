<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;

class DataDescriptor
{
    /**
     * Signature for the data descriptor record.
     */
    private const SIGNATURE = 0x08074b50;

    /**
     * Generate the binary representation of the data descriptor record.
     */
    public static function generate(
        int $crc32,
        int $compressedSize,
        int $uncompressedSize,
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U32Field::create($crc32),
            U32Field::create($compressedSize),
            U32Field::create($uncompressedSize),
        );
    }
}
