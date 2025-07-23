<?php

namespace Tests\Unit\Archives\Zip\Records;

use DateTimeImmutable;
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PHPUnit\Framework\TestCase;

class CentralDirectoryHeaderTest extends TestCase
{
    public function testCentralDirectoryHeader(): void
    {
        $time = new DateTimeImmutable('2022-01-01 01:01:01Z');

        $header = bin2hex(EndOfCentralDirectoryRecord::generate(
            0x603,
            0x002D,
            0x2222,
            0x08,
            $time->getTimestamp(),
            0x11111111,
            0x77777777,
            0x99999999,
            0,
            0,
            32,
            0x1234,
            'test.png',
            'some content',
            'some comment'
        ));

        $expected = '504b050603062d0022220800cda7cf61111111110a0032303034333138303731';

        $this->assertEquals($header, $expected);
    }
}
