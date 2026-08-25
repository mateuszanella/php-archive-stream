<?php

namespace PhpArchiveStream\Binary;

use InvalidArgumentException;

class VariableUInt64
{
    /**
     * Encode an unsigned integer using the 7z variable-length encoding.
     *
     * The first byte's leading 1-bits indicate the number of little-endian
     * extra bytes that follow. For example 0 => \x00, 128 => \x80\x80.
     *
     * @param  int  $value  The value to encode.
     * @return string The encoded bytes.
     *
     * @throws InvalidArgumentException If the value is negative.
     */
    public static function encode(int $value): string
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Value must be non-negative');
        }

        if ($value < 0x80) {
            return chr($value);
        }

        for ($extraBytes = 1; $extraBytes <= 7; $extraBytes++) {
            $bits = 7 * ($extraBytes + 1);

            if ($value < (1 << $bits)) {
                $marker = (0xFF << (8 - $extraBytes)) & 0xFF;
                $first = $marker | ($value >> (8 * $extraBytes));

                $result = chr($first);
                $low = $value & ((1 << (8 * $extraBytes)) - 1);

                for ($i = 0; $i < $extraBytes; $i++) {
                    $result .= chr(($low >> (8 * $i)) & 0xFF);
                }

                return $result;
            }
        }

        $result = chr(0xFF);

        for ($i = 0; $i < 8; $i++) {
            $result .= chr(($value >> (8 * $i)) & 0xFF);
        }

        return $result;
    }
}
