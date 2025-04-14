<?php

namespace Tests\Feature\Archives;

use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\IO\Output\TarOutputStream;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Archives\Tar
 */
class TarTest extends TestCase
{
    protected string $outputPath = './output.tar';
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
        $outputStream = new TarOutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->addFileFromPath('input1.txt', $this->inputPath1);
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testAddFileFromStream()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new TarOutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $resource = fopen($this->inputPath1, 'r');
        $tar->addFileFromStream('input1.txt', $resource);
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testAddFileFromContentString()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new TarOutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->addFileFromContentString('input1.txt', 'Hello World 1');
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testFinish()
    {
        $stream = fopen($this->outputPath, 'w');
        $outputStream = new TarOutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->finish();

        $reflection = new \ReflectionClass($tar);
        $property = $reflection->getProperty('writer');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($tar));
    }
}
