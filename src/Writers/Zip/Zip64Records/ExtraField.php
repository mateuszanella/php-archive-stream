<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U16Field;

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
