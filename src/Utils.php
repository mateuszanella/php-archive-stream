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
}
