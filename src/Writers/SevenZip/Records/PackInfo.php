<?php

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\VariableUInt64;

class PackInfo
{
    /**
     * Generate the PackInfo section of the header.
     *
     * @param  int  $packPos  The offset of the first pack stream.
     * @param  array<int, int>  $packedSizes  The size in bytes of each pack stream.
     */
    public static function generate(int $packPos, array $packedSizes): string
    {
        $result = "\x06" // kPackInfo
            .VariableUInt64::encode($packPos)
            .VariableUInt64::encode(count($packedSizes))
            ."\x09"; // kSize

        foreach ($packedSizes as $size) {
            $result .= VariableUInt64::encode($size);
        }

        return $result."\x00"; // kEnd
    }
}
