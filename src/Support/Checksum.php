<?php

declare(strict_types=1);

namespace PhpArchiveStream\Support;

/**
 * @internal
 */
class Checksum
{
    /**
     * Calculate the checksum of the given data.
     *
     * @param  string  $data  The data to checksum, treated as raw bytes.
     * @param  int|null  $size  The number of bytes to include. Defaults to the full byte length.
     */
    public static function tar(string $data, ?int $size = null): int
    {
        if ($size === null) {
            $size = strlen($data);
        }

        $checksum = 0;

        for ($i = 0; $i < $size; $i++) {
            $checksum += ord($data[$i]);
        }

        return $checksum;
    }
}
