<?php

namespace Tests\Feature\Archives;

use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Archives\SevenZip;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Archives\SevenZip
 */
class SevenZipTest extends TestCase
{
    protected string $outputPath = './output.7z';

    protected string $inputPath = './input.txt';

    protected function setUp(): void
    {
        if (! function_exists('xz_encode_init')) {
            $this->markTestSkipped('The xz extension is not available.');
        }

        foreach ([$this->outputPath, $this->inputPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        file_put_contents($this->inputPath, 'Hello World');
    }

    protected function tearDown(): void
    {
        foreach ([$this->outputPath, $this->inputPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function test_creates_seven_zip_archive_via_manager(): void
    {
        $manager = ArchiveManager::make();

        $archive = $manager->create($this->outputPath);

        $this->assertInstanceOf(SevenZip::class, $archive);
    }

    public function test_archive_has_valid_signature_and_header(): void
    {
        $manager = ArchiveManager::make();

        $archive = $manager->create($this->outputPath);
        $archive->addFileFromContentString('hello.txt', 'Hello World 1');
        $archive->addFileFromContentString('world.txt', 'Hello World 2');
        $archive->addFileFromContentString('empty.txt', '');
        $archive->finish();

        $data = file_get_contents($this->outputPath);

        $this->assertSame("7z\xBC\xAF\x27\x1C", substr($data, 0, 6));
        $this->assertSame("\x00\x04", substr($data, 6, 2));

        $startHeader = substr($data, 12, 20);
        $startHeaderCrc = unpack('V', substr($data, 8, 4))[1];

        $this->assertSame(crc32($startHeader), $startHeaderCrc);

        ['offset' => $offset, 'size' => $size, 'crc' => $headerCrc] = unpack('Poffset/Psize/Vcrc', $startHeader);

        $this->assertSame(strlen($data), 32 + $offset + $size);

        $header = substr($data, 32 + $offset, $size);

        $this->assertSame(crc32($header), $headerCrc);
        $this->assertSame("\x01", $header[0]);
    }
}
