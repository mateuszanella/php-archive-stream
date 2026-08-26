<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\SevenZip\Records;

use PhpArchiveStream\Binary\Packer;
use PhpArchiveStream\Binary\U32Field;
use PhpArchiveStream\Binary\U64Field;

/**
 * @internal
 */
class SignatureHeader
{
    /**
     * The 7z archive signature: '7', 'z', 0xBC, 0xAF, 0x27, 0x1C.
     */
    public const SIGNATURE = "\x37\x7A\xBC\xAF\x27\x1C";

    /**
     * The archive version (major = 0, minor = 4).
     */
    public const VERSION = "\x00\x04";

    /**
     * Generate the 32-byte signature header.
     *
     * @param  int  $nextHeaderOffset  The offset of the header from the end of the signature header.
     * @param  int  $nextHeaderSize  The size of the header in bytes.
     * @param  int  $nextHeaderCrc  The CRC32 of the header.
     */
    public static function generate(int $nextHeaderOffset, int $nextHeaderSize, int $nextHeaderCrc): string
    {
        $startHeader = Packer::pack(
            U64Field::create($nextHeaderOffset),
            U64Field::create($nextHeaderSize),
            U32Field::create($nextHeaderCrc),
        );

        return self::SIGNATURE
            .self::VERSION
            .Packer::pack(U32Field::create(crc32($startHeader)))
            .$startHeader;
    }
}
