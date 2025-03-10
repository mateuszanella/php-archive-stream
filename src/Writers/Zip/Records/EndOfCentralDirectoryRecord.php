<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U16Field;
use PhpArchiveStream\Writers\Zip\Records\Fields\U32Field;

class EndOfCentralDirectoryRecord
{
    public const SIGNATURE = 0x06054b50;

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
