<?php

namespace PhpArchiveStream\Binary;

class U64Field extends Field
{
    public static string $format = 'P';

    public readonly int|string $value;

    /**
     * Constructor for the U64Field class.
     *
     * @param  int|string  $value  The value of the field, must be a valid unsigned 64-bit integer.
     */
    public function __construct(int|string $value)
    {
        $this->value = $value;
    }

    public static function create($value): static
    {
        return new static($value);
    }
}
