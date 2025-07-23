<?php

namespace PhpArchiveStream\Binary;

class Packer
{
    /**
     * Packs the given fields into a binary string.
     *
     * @param  Field[]  ...$fields  The fields to pack.
     * @return string The packed binary string.
     */
    public static function pack(...$fields): string
    {
        $format = '';
        $values = [];

        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $format .= $field::$format;
                $values[] = $field->value;
            }
        }

        return pack($format, ...$values);
    }
}
