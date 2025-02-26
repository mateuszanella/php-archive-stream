<?php

namespace LaravelFileStream;

class Utils
{
    public static function checksum(string $data, ?int $size = null): int
    {
        $checksum = 0;

        if ($size === null) {
            $size = strlen($data);
        }

        for ($i = 0; $i < $size; $i++) {
            $checksum += ord($data[$i]);
        }

        return $checksum;
    }
}
