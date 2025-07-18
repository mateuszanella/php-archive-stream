<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;

class DataDescriptor
{
    /**
     * Signature for the data descriptor record.
     */
    private const SIGNATURE = 0x08074b50;

    /**
     * Generate the binary representation of the Zip64 data descriptor record.
     */
    public static function generate(
        int $crc32,
        int $compressedSize,
        int $uncompressedSize,
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U32Field::create($crc32),
            U64Field::create($compressedSize),
            U64Field::create($uncompressedSize),
        );
    }
}
