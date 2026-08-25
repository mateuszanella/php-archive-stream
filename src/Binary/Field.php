<?php

declare(strict_types=1);

namespace PhpArchiveStream\Binary;

use InvalidArgumentException;

/**
 * @internal
 *
 * @phpstan-consistent-constructor
 */
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
     * @param  int|string  $value  The value of the field.
     */
    public function __construct(int|string $value)
    {
        $this->value = $value;
    }

    /**
     * Create a new field instance from the given value.
     *
     * @param  int|string  $value  The value of the field.
     */
    abstract public static function create($value): static;

    /**
     * Validates the value of the field.
     *
     * @param  mixed  $value  The value to validate.
     *
     * @throws InvalidArgumentException If the value is invalid.
     */
    protected static function validate($value): void {}
}
