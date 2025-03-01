<?php

namespace Tests\Unit;

use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\Tar\IO\InputStream;
use PhpArchiveStream\Writers\Tar\IO\OutputStream;
use PHPUnit\Framework\TestCase;

class TarWriterTest extends TestCase
{
    protected TarWriter $tarWriter;
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

        $this->tarWriter = new TarWriter($this->outputPath);

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

    public function testConstructor()
    {
        $reflection = new \ReflectionClass($this->tarWriter);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $this->assertInstanceOf(OutputStream::class, $property->getValue($this->tarWriter));
    }

    public function testAddFile()
    {
        $inputStream = InputStream::open($this->inputPath1);
        $this->tarWriter->addFile($inputStream, 'input1.txt');

        $reflection = new \ReflectionClass($this->tarWriter);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $outputStream = $property->getValue($this->tarWriter);

        $this->assertNotNull($outputStream);
    }

    public function testFinish()
    {
        $this->tarWriter->finish();

        $reflection = new \ReflectionClass($this->tarWriter);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->tarWriter));
    }

    public function testOutputFileExistsAfterFinish()
    {
        $inputStream = InputStream::open($this->inputPath1);
        $this->tarWriter->addFile($inputStream, 'input1.txt');
        $this->tarWriter->finish();

        $this->assertFileExists($this->outputPath);
    }
}
