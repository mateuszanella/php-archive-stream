<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Utils;
use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U16Field;
use PhpArchiveStream\Writers\Zip\Records\Fields\U32Field;

class LocalFileHeader
{
    public const SIGNATURE = 0x04034b50;

    public static function generate(
        int $minimumVersion,
        int $generalPurposeBitFlag,
        int $compressionMethod,
        int $lastModificationUnixTime,
        int $crc32,
        int $compressedSize,
        int $uncompressedSize,
        string $fileName,
        ?string $extraField = ''
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U16Field::create($minimumVersion),
            U16Field::create($generalPurposeBitFlag),
            U16Field::create($compressionMethod),
            U32Field::create(Utils::convertUnixToDosTime($lastModificationUnixTime)),
            U32Field::create($crc32),
            U32Field::create($compressedSize),
            U32Field::create($uncompressedSize),
            U16Field::create(strlen($fileName)),
            U16Field::create(strlen($extraField)),
        ) . $fileName . $extraField;
    }
}
