<?php

namespace PhpArchiveStream;

class Utils
{
    public static function checksum(string $data, ?int $size = null): int
    {
        $checksum = 0;

        if ($size === null) {
            $size = mb_strlen($data);
        }

        for ($i = 0; $i < $size; $i++) {
            $checksum += ord($data[$i]);
        }

        return $checksum;
    }

    public static function convertUnixToDosTime(int $unixTime): int
    {
        $time = getdate($unixTime);

        $year = $time['year'] - 1980;
        $month = $time['mon'];
        $day = $time['mday'];
        $hours = $time['hours'];
        $minutes = $time['minutes'];
        $seconds = $time['seconds'] >> 1;

        return ($year << 25)
            | ($month << 21)
            | ($day << 16)
            | ($hours << 11)
            | ($minutes << 5)
            | $seconds;
    }

    /**
     * Convert a Unix timestamp to a Windows FILETIME value.
     *
     * FILETIME is the number of 100-nanosecond intervals since 1601-01-01.
     */
    public static function toFileTime(int $unixTime): int
    {
        return $unixTime * 10000000 + 116444736000000000;
    }

    /**
     * Encode a string as UTF-16 little-endian bytes.
     */
    public static function toUtf16Le(string $value): string
    {
        return mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');
    }
}
