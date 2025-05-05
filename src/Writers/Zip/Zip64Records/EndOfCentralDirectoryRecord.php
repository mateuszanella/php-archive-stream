<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U16Field;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;

class EndOfCentralDirectoryRecord
{
    public const SIGNATURE = 0x06064b50;

    public static function generate(
        int    $versionMadeBy,
        int    $versionNeededToExtract,
        int    $numberOfThisDisk,
        int    $numberOfTheDiskWithTheStartOfTheCentralDirectory,
        int    $numberOfCentralDirectoryEntriesOnThisDisk,
        int    $numberOfCentralDirectoryEntries,
        int    $centralDirectorySize,
        int    $centralDirectoryOffsetOnDisk,
        string $extensibleDataSector
    ): string {
        /**
         * Size = (SizeOfFixedFields - 12) + SizeOfVariableData.
         */
        $sizeOfEndOfCentralDirectoryRecord = 44 + strlen($extensibleDataSector);

        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U64Field::create($sizeOfEndOfCentralDirectoryRecord),
            U16Field::create($versionMadeBy),
            U16Field::create($versionNeededToExtract),
            U32Field::create($numberOfThisDisk),
            U32Field::create($numberOfTheDiskWithTheStartOfTheCentralDirectory),
            U64Field::create($numberOfCentralDirectoryEntriesOnThisDisk),
            U64Field::create($numberOfCentralDirectoryEntries),
            U64Field::create($centralDirectorySize),
            U64Field::create($centralDirectoryOffsetOnDisk),
        ) . $extensibleDataSector;
    }
}
