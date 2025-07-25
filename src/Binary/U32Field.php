<?php

namespace PhpArchiveStream\Binary;

class U32Field extends Field
{
    public const MAX_UNSIGNED_LONG = 0xFFFFFFFF;

    public static string $format = 'V';

    public readonly int|string $value;

    /**
     * Constructor for the U32Field class.
     *
     * @param  int  $value  The value of the field, must be between 0 and 0xFFFFFFFF.
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
        if ($value < 0 || $value > static::MAX_UNSIGNED_LONG) {
            $value = static::MAX_UNSIGNED_LONG;
        }
    }
}
