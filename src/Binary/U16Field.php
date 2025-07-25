<?php

namespace PhpArchiveStream\Binary;

class U16Field extends Field
{
    public const MAX_UNSIGNED_SHORT = 0xFFFF;

    public static string $format = 'v';

    public readonly int|string $value;

    /**
     * Constructor for the U16Field class.
     *
     * @param  int  $value  The value of the field, must be between 0 and 0xFFFF.
     */
    public function __construct(int $value)
    {
        static::validate($value);

        $this->value = $value;
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
