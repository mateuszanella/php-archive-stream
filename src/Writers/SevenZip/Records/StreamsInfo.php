<?php

namespace PhpArchiveStream\Writers\SevenZip\Records;

class StreamsInfo
{
    /**
     * Generate the MainStreamsInfo section of the header.
     *
     * @param  array<int, int>  $packedSizes  The size of each pack stream.
     * @param  array<int, string>  $folderBytes  The serialized folder data.
     * @param  array<int, int>  $unpackSizes  The unpacked size of each folder.
     * @param  array<int, int>  $crcs  The CRC32 of each file's unpacked data.
     */
    public static function generate(array $packedSizes, array $folderBytes, array $unpackSizes, array $crcs): string
    {
        $result = "\x04" // kMainStreamsInfo
            .PackInfo::generate(0, $packedSizes)
            .UnpackInfo::generate($folderBytes, $unpackSizes);

        if ($crcs !== []) {
            $result .= SubStreamsInfo::generate($crcs);
        }

        return $result."\x00"; // kEnd
    }
}
