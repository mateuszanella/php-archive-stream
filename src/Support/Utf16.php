<?php

declare(strict_types=1);

namespace PhpArchiveStream\Support;

/**
 * @internal
 */
class Utf16
{
    /**
     * Encode a UTF-8 string as UTF-16 little-endian bytes.
     */
    public static function toUtf16Le(string $value): string
    {
        return mb_convert_encoding($value, 'UTF-16LE', 'UTF-8');
    }
}
