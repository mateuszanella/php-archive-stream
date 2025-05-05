<?php

namespace PhpArchiveStream\Binary;

class Packer
{
    public static function pack(... $fields): string
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
