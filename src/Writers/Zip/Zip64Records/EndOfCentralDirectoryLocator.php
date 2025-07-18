<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;

class EndOfCentralDirectoryLocator
{
    /**
     * Signature for the end of central directory locator record.
     */
    private const SIGNATURE = 0x07064b50;

    /**
     * Generate the binary representation of the Zip64 end of central directory locator record.
     */
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
