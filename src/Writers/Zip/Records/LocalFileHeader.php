<?php

namespace PhpArchiveStream\Writers\Zip\Records;

class LocalFileHeader
{
    public const SIGNATURE = 0x04034b50;

    public static function get(

    ): string {
        return Packer::pack(
            // Field::create('V', self::SIGNATURE),
        );
    }
}
