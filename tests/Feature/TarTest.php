<?php

namespace Tests\Feature;

use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Archives\Tar
 */
class TarTest extends TestCase
{
    protected Tar $tar;
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

        $tarWriter = TarWriter::create($this->outputPath);
        $this->tar = new Tar($this->outputPath, $tarWriter);
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
        $this->tar->addFileFromPath('input1.txt', $this->inputPath1);
        $this->tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testAddFileFromStream()
    {
        $resource = fopen($this->inputPath1, 'r');
        $this->tar->addFileFromStream('input1.txt', $resource);
        $this->tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testAddFileFromContentString()
    {
        $this->tar->addFileFromContentString('input1.txt', 'Hello World 1');
        $this->tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function testFinish()
    {
        $this->tar->finish();

        $reflection = new \ReflectionClass($this->tar);
        $property = $reflection->getProperty('writer');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->tar));
    }
}
