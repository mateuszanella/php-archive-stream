<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U32Field;
use PhpArchiveStream\Writers\Zip\Records\Fields\U64Field;

class EndOfCentralDirectoryLocator
{
    private const SIGNATURE = 0x07064b50;

    public static function generate(
        int $numberOfTheDiskWithZip64CentralDirectoryStart,
        int $zip64centralDirectoryStartOffsetOnDisk,
        int $totalNumberOfDisks,
    ): string {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U32Field::create($numberOfTheDiskWithZip64CentralDirectoryStart),
            U64Field::create($zip64centralDirectoryStartOffsetOnDisk),
            U32Field::create($totalNumberOfDisks),
        );
    }
}
