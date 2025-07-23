<?php

namespace Tests\Unit\Archives\Zip\Records;

use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PHPUnit\Framework\TestCase;

class EndOfCentralDirectoryRecordTest extends TestCase
{
    public function test_end_of_central_directory_record(): void
    {
        $header = bin2hex(EndOfCentralDirectoryRecord::generate(
            0x00,
            0x00,
            0x10,
            0x10,
            0x22,
            0x33,
            'foo'
        ));

        $expected = '504b0506'
        .'0000'
        .'0000'
        .'1000'
        .'1000'
        .'22000000'
        .'33000000'
        .'0300'
        .bin2hex('foo');

        $this->assertEquals($header, $expected);
    }
}
