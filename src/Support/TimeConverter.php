<?php

declare(strict_types=1);

namespace PhpArchiveStream\Support;

/**
 * @internal
 */
class TimeConverter
{
    /**
     * Convert a Unix timestamp to the bit-packed DOS time format used by ZIP.
     */
    public static function toDosTime(int $unixTime): int
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
     * Convert a Unix timestamp to a Windows FILETIME value, which is the
     * number of 100-nanosecond intervals since 1601-01-01.
     */
    public static function toFileTime(int $unixTime): int
    {
        return $unixTime * 10000000 + 116444736000000000;
    }
}
