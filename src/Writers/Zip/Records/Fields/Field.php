<?php

namespace PhpArchiveStream\Writers\Zip\Records\Fields;

abstract class Field
{
    public static string $format;
    public readonly int|string $value;

    abstract public static function create($value): static;

    protected static function validate($value): void
    {
        return;
    }
}
