<?php

namespace Tests\Unit\Archives\Zip\Zip64Records;

use PhpArchiveStream\Writers\Zip\Zip64Records\ExtraField;
use PHPUnit\Framework\TestCase;

class Zip64ExtraFieldTest extends TestCase
{
    public function testZip64ExtraField(): void
    {
        $header = bin2hex(ExtraField::generate(
            originalSize: (0x77777777 << 32) + 0x66666666,
            compressedSize: (0x99999999 << 32) + 0x88888888,
            relativeHeaderOffset: (0x22222222 << 32) + 0x11111111,
            diskStartNumber: 0x33333333,
        ));

        $expected = '0100' .
            '1c00' .
            '6666666677777777' .
            '8888888899999999' .
            '1111111122222222' .
            '33333333';

        $this->assertSame($expected, $header);
    }
}
