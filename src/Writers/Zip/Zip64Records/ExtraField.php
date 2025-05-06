<?php

namespace PhpArchiveStream\Writers\Zip\Zip64Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U16Field;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;

class ExtraField
{
    public const SIGNATURE = 0x0001;

    public static function generate(
        ?int $originalSize = null,
        ?int $compressedSize = null,
        ?int $relativeHeaderOffset = null,
        ?int $diskStartNumber = null,
    ): string {
        $fieldSize = ($originalSize === null ? 0 : 8)
            + ($compressedSize === null ? 0 : 8)
            + ($relativeHeaderOffset === null ? 0 : 8)
            + ($diskStartNumber === null ? 0 : 4);

        $fields = [
            U16Field::create(self::SIGNATURE),
            U16Field::create($fieldSize),
        ];

        if ($originalSize) {
            $fields[] = U64Field::create($originalSize);
        }

        if ($compressedSize) {
            $fields[] = U64Field::create($compressedSize);
        }

        if ($relativeHeaderOffset) {
            $fields[] = U64Field::create($relativeHeaderOffset);
        }

        if ($diskStartNumber) {
            $fields[] = U32Field::create($diskStartNumber);
        }

        return Packer::pack(
            ...$fields,
        );
    }
}
