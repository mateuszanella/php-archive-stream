<?php

declare(strict_types=1);

namespace Tests\Unit\Writers\Tar;

use InvalidArgumentException;
use PhpArchiveStream\Support\Checksum;
use PhpArchiveStream\Writers\Tar\Header;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Writers\Tar\Header
 */
class HeaderTest extends TestCase
{
    public function test_generates_a_512_byte_header_for_ascii_and_multibyte_names()
    {
        $names = [
            'input1.txt',
            'Orçamento – Ação [assinado].pdf',
            str_repeat('é', 50),
        ];

        foreach ($names as $name) {
            $header = Header::generate($name, 'Documentos/Ação', 11);

            $this->assertSame(512, strlen($header));
            $this->assertSame($name, rtrim(substr($header, 0, 100), "\0"));
            $this->assertSame('Documentos/Ação', rtrim(substr($header, 345, 155), "\0"));
        }
    }

    public function test_header_checksum_is_valid()
    {
        $header = Header::generate('Orçamento – Ação [assinado].pdf', 'Documentos/Ação', 11);
        $headerWithBlankChecksum = substr_replace($header, str_repeat(' ', 8), 148, 8);

        $storedChecksum = (int) octdec(trim(substr($header, 148, 8), " \0"));

        $this->assertSame(Checksum::tar($headerWithBlankChecksum, 512), $storedChecksum);
    }

    public function test_throws_when_file_name_exceeds_100_bytes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TAR file name must not exceed 100 bytes.');

        Header::generate(str_repeat('a', 101), '', 0);
    }

    public function test_throws_when_prefix_exceeds_155_bytes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TAR file prefix must not exceed 155 bytes.');

        Header::generate('input1.txt', str_repeat('a', 156), 0);
    }
}
