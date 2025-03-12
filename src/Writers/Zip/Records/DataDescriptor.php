<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U32Field;

class DataDescriptor
{
    private const SIGNATURE = 0x08074b50;

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
