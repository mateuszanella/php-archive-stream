<?php

namespace PhpArchiveStream\Writers\Zip\Records\Fields;

class U64Field extends Field
{
    public static string $format = 'P';

    public readonly int|string $value;

    public function __construct(int|string $value)
    {
        $this->value = $value;
    }

    public static function create($value): static
    {
        return new static($value);
    }
}
