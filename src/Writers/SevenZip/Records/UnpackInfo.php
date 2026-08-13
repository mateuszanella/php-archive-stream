<?php

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\VariableUInt64;

class UnpackInfo
{
    /**
     * Generate the UnpackInfo section of the header.
     *
     * @param  array<int, string>  $folderBytes  The serialized folder data.
     * @param  array<int, int>  $unpackSizes  The unpacked size of each folder.
     */
    public static function generate(array $folderBytes, array $unpackSizes): string
    {
        $result = "\x07" // kUnpackInfo
            ."\x0B" // kFolder
            .VariableUInt64::encode(count($folderBytes))
            ."\x00" // External = 0
            .implode('', $folderBytes)
            ."\x0C"; // kCodersUnpackSize

        foreach ($unpackSizes as $size) {
            $result .= VariableUInt64::encode($size);
        }

        return $result."\x00"; // kEnd
    }
}
