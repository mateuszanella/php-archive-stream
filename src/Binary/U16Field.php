<?php

declare(strict_types=1);

namespace PhpArchiveStream\Binary;

/**
 * @internal
 */
class U16Field extends Field
{
    public const MAX_UNSIGNED_SHORT = 0xFFFF;

    public static string $format = 'v';

    /**
     * Constructor for the U16Field class.
     *
     * @param  int|string  $value  The value of the field, must be between 0 and 0xFFFF.
     */
    public function __construct(int|string $value)
    {
        static::validate($value);

        parent::__construct($value);
    }

    public static function create($value): static
    {
        return new static($value);
    }

    protected static function validate($value): void
    {
        if ($value < 0 || $value > static::MAX_UNSIGNED_SHORT) {
            $value = static::MAX_UNSIGNED_SHORT;
        }
    }
}
