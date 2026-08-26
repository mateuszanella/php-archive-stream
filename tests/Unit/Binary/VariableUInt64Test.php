<?php

declare(strict_types=1);

namespace Tests\Unit\Binary;

use InvalidArgumentException;
use PhpArchiveStream\Binary\VariableUInt64;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VariableUInt64Test extends TestCase
{
    public static function encodingProvider(): array
    {
        return [
            [0, '00'],
            [1, '01'],
            [127, '7f'],
            [128, '8080'],
            [255, '80ff'],
            [256, '8100'],
            [16383, 'bfff'],
            [16384, 'c00040'],
            [65535, 'c0ffff'],
            [65536, 'c10000'],
            [4294967295, 'f0ffffffff'],
            [4294967296, 'f100000000'],
        ];
    }

    #[DataProvider('encodingProvider')]
    public function test_encoding(int $value, string $expectedHex): void
    {
        $this->assertSame($expectedHex, bin2hex(VariableUInt64::encode($value)));
    }

    public function test_negative_value_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        VariableUInt64::encode(-1);
    }
}
