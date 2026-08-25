<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;
use PhpArchiveStream\Binary\VariableUInt64;
use PhpArchiveStream\Support\TimeConverter;
use PhpArchiveStream\Support\Utf16;

/**
 * @internal
 */
class FilesInfo
{
    /**
     * Generate the FilesInfo section of the header.
     *
     * @param  array<int, array{name: string, mtime: int, attributes: int}>  $files
     * @param  array<int, bool>  $emptyStreams  Whether each file has no data stream.
     */
    public static function generate(array $files, array $emptyStreams): string
    {
        $numFiles = count($files);

        $result = "\x05" // kFilesInfo
            .VariableUInt64::encode($numFiles);

        if (in_array(true, $emptyStreams, true)) {
            $bits = self::packBits($emptyStreams);

            $result .= "\x0E" // kEmptyStream
                .VariableUInt64::encode(strlen($bits))
                .$bits;
        }

        $result .= self::generateNames($files);
        $result .= self::generateTimes($files);
        $result .= self::generateAttributes($files);

        return $result."\x00"; // kEnd
    }

    /**
     * Generate the kName property.
     *
     * @param  array<int, array{name: string, mtime: int, attributes: int}>  $files
     */
    protected static function generateNames(array $files): string
    {
        $names = '';

        foreach ($files as $file) {
            $names .= Utf16::toUtf16Le($file['name'])."\x00\x00";
        }

        return "\x11" // kName
            .VariableUInt64::encode(strlen($names) + 1)
            ."\x00" // External = 0
            .$names;
    }

    /**
     * Generate the kMTime property.
     *
     * @param  array<int, array{name: string, mtime: int, attributes: int}>  $files
     */
    protected static function generateTimes(array $files): string
    {
        $result = "\x14" // kMTime
            .VariableUInt64::encode(count($files) * 8 + 2)
            ."\x01" // AllAreDefined = 1
            ."\x00"; // External = 0

        foreach ($files as $file) {
            $result .= Packer::pack(U64Field::create(TimeConverter::toFileTime($file['mtime'])));
        }

        return $result;
    }

    /**
     * Generate the kAttributes property.
     *
     * @param  array<int, array{name: string, mtime: int, attributes: int}>  $files
     */
    protected static function generateAttributes(array $files): string
    {
        $result = "\x15" // kAttributes
            .VariableUInt64::encode(count($files) * 4 + 2)
            ."\x01" // AllAreDefined = 1
            ."\x00"; // External = 0

        foreach ($files as $file) {
            $result .= Packer::pack(U32Field::create($file['attributes']));
        }

        return $result;
    }

    /**
     * Pack booleans into bytes, most-significant bit first.
     *
     * @param  array<int, bool>  $bits
     */
    protected static function packBits(array $bits): string
    {
        $result = '';
        $byte = 0;
        $index = 0;

        foreach ($bits as $bit) {
            if ($bit) {
                $byte |= 1 << (7 - ($index % 8));
            }

            $index++;

            if ($index % 8 === 0) {
                $result .= chr($byte);
                $byte = 0;
            }
        }

        if ($index % 8 !== 0) {
            $result .= chr($byte);
        }

        return $result;
    }
}
