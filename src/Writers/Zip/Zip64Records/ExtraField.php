<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U16Field;

class ExtraField
{
    public const SIGNATURE = 0x0001;

    public static function generate(
        int $originalSize,
        int $compressedSize,
        int $relativeHeaderOffset,
        int $diskStartNumber,
    ): string {
        return Packer::pack(
            U16Field::create(self::SIGNATURE),
        );
    }
}
