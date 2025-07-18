<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U16Field;
use PhpArchiveStream\Binary\U32Field;

class EndOfCentralDirectoryRecord
{
    /**
     * Signature for the end of central directory record.
     */
    public const SIGNATURE = 0x06054b50;

    /**
     * Generate the binary representation of the end of central directory record.
     */
    public static function generate(
        int     $diskNumber,
        int     $diskStart,
        int     $numberOfCentralDirectoryRecords,
        int     $totalCentralDirectoryRecords,
        int     $centralDirectorySize,
        int     $centralDirectoryOffset,
        ?string $comment = ''
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U16Field::create($diskNumber),
            U16Field::create($diskStart),
            U16Field::create($numberOfCentralDirectoryRecords),
            U16Field::create($totalCentralDirectoryRecords),
            U32Field::create($centralDirectorySize),
            U32Field::create($centralDirectoryOffset),
            U16Field::create(strlen($comment)),
        ) . $comment;
    }
}
