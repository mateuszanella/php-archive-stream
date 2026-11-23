<?php

declare(strict_types=1);

namespace Tests\Feature\Archives;

use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\Writers\Zip\ZipWriter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \PhpArchiveStream\Archives\Zip
 */
class ZipTest extends TestCase
{
    protected string $outputPath = './output.zip';

    protected string $inputPath1 = './input1.txt';

    protected string $inputPath2 = './input2.txt';

    protected function setUp(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }

        if (file_exists($this->inputPath1)) {
            unlink($this->inputPath1);
        }

        if (file_exists($this->inputPath2)) {
            unlink($this->inputPath2);
        }

        file_put_contents($this->inputPath1, 'Hello World 1');
        file_put_contents($this->inputPath2, 'Hello World 2');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->outputPath)) {
            unlink($this->outputPath);
        }

        if (file_exists($this->inputPath1)) {
            unlink($this->inputPath1);
        }

        if (file_exists($this->inputPath2)) {
            unlink($this->inputPath2);
        }
    }

    public function test_add_file_from_path()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromPath('input1.txt', $this->inputPath1);
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_stream()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $resource = fopen($this->inputPath1, 'r');
        $zip->addFileFromStream('input1.txt', $resource);
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_content_string()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromContentString('input1.txt', 'Hello World 1');
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_language_encoding_flag_is_set_for_utf8_file_names()
    {
        $fileNames = [
            'input1.txt',
            'Orçamento – Ação [assinado].pdf',
        ];

        foreach ($fileNames as $fileName) {
            $stream = fopen($this->outputPath, 'w');
            $outputStream = new OutputStream($stream);
            $zipWriter = new ZipWriter($outputStream);
            $zip = new Zip($zipWriter);

            $zip->addFileFromContentString($fileName, 'Hello World 1');
            $zip->finish();

            $archive = file_get_contents($this->outputPath);
            $this->assertIsString($archive);

            $endOfCentralDirectoryOffset = strlen($archive) - 22;
            $this->assertSame("PK\x05\x06", substr($archive, $endOfCentralDirectoryOffset, 4));

            $centralDirectoryOffset = unpack('V', $archive, $endOfCentralDirectoryOffset + 16)[1];

            $localFlags = unpack('v', $archive, 6)[1];
            $centralDirectoryFlags = unpack('v', $archive, $centralDirectoryOffset + 8)[1];

            $this->assertSame(0x0808, $localFlags);
            $this->assertSame(0x0808, $centralDirectoryFlags);

            $localNameLength = unpack('v', $archive, 26)[1];
            $this->assertSame($fileName, substr($archive, 30, $localNameLength));
            $this->assertSame(strlen($fileName), $localNameLength);

            $centralDirectoryNameLength = unpack('v', $archive, $centralDirectoryOffset + 28)[1];
            $this->assertSame(
                $fileName,
                substr($archive, $centralDirectoryOffset + 46, $centralDirectoryNameLength)
            );
            $this->assertSame(strlen($fileName), $centralDirectoryNameLength);
        }
    }

    public function test_finish()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->finish();

        $reflection = new ReflectionClass($zip);
        $property = $reflection->getProperty('writer');
        $this->assertNull($property->getValue($zip));
    }
}
