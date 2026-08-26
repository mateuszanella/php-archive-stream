<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;

/**
 * @internal
 */
class SubStreamsInfo
{
    /**
     * Generate the SubStreamsInfo section of the header for a non-solid archive.
     *
     * Each folder maps to exactly one sub-stream, so no unpack stream counts or
     * sub-stream sizes are needed. The CRC32 of every file is stored here.
     *
     * @param  array<int, int>  $crcs  The CRC32 of each file's unpacked data.
     */
    public static function generate(array $crcs): string
    {
        $result = "\x08" // kSubStreamsInfo
            ."\x0A" // kCRC
            ."\x01"; // AllAreDefined = 1

        foreach ($crcs as $crc) {
            $result .= Packer::pack(U32Field::create($crc));
        }

        return $result."\x00"; // kEnd
    }
}
