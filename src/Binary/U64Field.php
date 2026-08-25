<?php

declare(strict_types=1);

namespace PhpArchiveStream\Binary;

/**
 * @internal
 */
class U64Field extends Field
{
    public static string $format = 'P';

    /**
     * Constructor for the U64Field class.
     *
     * @param  int|string  $value  The value of the field, must be a valid unsigned 64-bit integer.
     */
    public function __construct(int|string $value)
    {
        parent::__construct($value);
    }

    public static function create($value): static
    {
        return new static($value);
    }
}
