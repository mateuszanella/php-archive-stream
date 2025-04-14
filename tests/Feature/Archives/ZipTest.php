<?php

namespace Tests\Feature\Archives;

use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\Writers\Zip\ZipWriter;
use PHPUnit\Framework\TestCase;

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

    public function testAddFileFromPath()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromPath('input1.txt', $this->inputPath1);
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testAddFileFromStream()
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

    public function testAddFileFromContentString()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->addFileFromContentString('input1.txt', 'Hello World 1');
        $zip->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testFinish()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new OutputStream($stream);
        $zipWriter = new ZipWriter($outputStream);
        $zip = new Zip($zipWriter);

        $zip->finish();

        $reflection = new \ReflectionClass($zip);
        $property = $reflection->getProperty('writer');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($zip));
    }
}
