<?php

namespace PhpArchiveStream\Writers\Zip\Records;

use PhpArchiveStream\Writers\Zip\Records\Fields\Packer;
use PhpArchiveStream\Writers\Zip\Records\Fields\U16Field;
use PhpArchiveStream\Writers\Zip\Records\Fields\U32Field;

// i will create an empty zip
class EndOfCentralDirectoryRecord
{
    public const SIGNATURE = 0x06054b50;

    public static function generate(): string
    {
        return Packer::pack(
            U32Field::create(self::SIGNATURE),
            U16Field::create(0),
            U16Field::create(0),
            U16Field::create(0),
            U16Field::create(0),
            U32Field::create(0),
            U32Field::create(0),
            U16Field::create(0),
        );
    }
}
