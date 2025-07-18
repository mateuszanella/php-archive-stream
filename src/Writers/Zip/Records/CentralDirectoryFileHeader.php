<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Utils;
use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U16Field;
use PhpArchiveStream\Binary\U32Field;

class CentralDirectoryFileHeader
{
    /**
     * Signature for the central directory file header.
     */
    public const SIGNATURE = 0x02014b50;

    /**
     * Generate the binary representation of the central directory file header.
     */
    public static function generate(
        int     $versionMadeBy,
        int     $minimumVersion,
        int     $generalPurposeBitFlag,
        int     $compressionMethod,
        int     $lastModificationUnixTime,
        int     $crc32,
        int     $compressedSize,
        int     $uncompressedSize,
        int     $diskNumberStart,
        int     $internalFileAttributes,
        int     $externalFileAttributes,
        int     $relativeOffsetOfLocalHeader,
        string  $fileName,
        ?string $extraField = '',
        ?string $fileComment = ''
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U16Field::create($versionMadeBy),
            U16Field::create($minimumVersion),
            U16Field::create($generalPurposeBitFlag),
            U16Field::create($compressionMethod),
            U32Field::create(Utils::convertUnixToDosTime($lastModificationUnixTime)),
            U32Field::create($crc32),
            U32Field::create($compressedSize),
            U32Field::create($uncompressedSize),
            U16Field::create(strlen($fileName)),
            U16Field::create(strlen($extraField)),
            U16Field::create(strlen($fileComment)),
            U16Field::create($diskNumberStart),
            U16Field::create($internalFileAttributes),
            U32Field::create($externalFileAttributes),
            U32Field::create($relativeOffsetOfLocalHeader),
        ) . $fileName . $extraField . $fileComment;
    }
}
