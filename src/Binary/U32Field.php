<?php

declare(strict_types=1);

namespace PhpArchiveStream\Binary;

/**
 * @internal
 */
class U32Field extends Field
{
    public const MAX_UNSIGNED_LONG = 0xFFFFFFFF;

    public static string $format = 'V';

    /**
     * Constructor for the U32Field class.
     *
     * @param  int|string  $value  The value of the field, must be between 0 and 0xFFFFFFFF.
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
        if ($value < 0 || $value > static::MAX_UNSIGNED_LONG) {
            $value = static::MAX_UNSIGNED_LONG;
        }
    }
}
