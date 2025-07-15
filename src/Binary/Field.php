<?php

namespace PhpArchiveStream\Binary;

abstract class Field
{
    /**
     * The format string used for packing the field. Must be a valid format for the `pack` function.
     */
    public static string $format;

    /**
     * The value of the field to be packed.
     */
    public readonly int|string $value;

    /**
     * Constructor for the Field class.
     *
     * @param  int|string  $value The value of the field.
     */
    abstract public static function create($value): static;

    /**
     * Validates the value of the field.
     *
     * @param  mixed  $value The value to validate.
     * @throws \InvalidArgumentException If the value is invalid.
     */
    protected static function validate($value): void
    {
        return;
    }
}
