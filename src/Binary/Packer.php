<?php

declare(strict_types=1);

namespace PhpArchiveStream\Binary;

/**
 * @internal
 */
class Packer
{
    /**
     * Packs the given fields into a binary string.
     *
     * @param  Field  ...$fields  The fields to pack.
     * @return string The packed binary string.
     */
    public static function pack(Field ...$fields): string
    {
        $format = '';
        $values = [];

        foreach ($fields as $field) {
            $format .= $field::$format;
            $values[] = $field->value;
        }

        return pack($format, ...$values);
    }
}
