<?php

namespace PhpArchiveStream\Writers\SevenZip\Records;

class Header
{
    /**
     * Generate the uncompressed 7z header.
     *
     * @param  array<int, int>  $packedSizes  The size of each pack stream.
     * @param  array<int, string>  $folderBytes  The serialized folder data.
     * @param  array<int, int>  $unpackSizes  The unpacked size of each folder.
     * @param  array<int, int>  $crcs  The CRC32 of each file's unpacked data.
     * @param  array<int, array{name: string, mtime: int, attributes: int}>  $files
     * @param  array<int, bool>  $emptyStreams  Whether each file has no data stream.
     */
    public static function generate(
        array $packedSizes,
        array $folderBytes,
        array $unpackSizes,
        array $crcs,
        array $files,
        array $emptyStreams
    ): string {
        return "\x01" // kHeader (uncompressed)
            .StreamsInfo::generate($packedSizes, $folderBytes, $unpackSizes, $crcs)
            .FilesInfo::generate($files, $emptyStreams)
            ."\x00"; // kEnd
    }
}
