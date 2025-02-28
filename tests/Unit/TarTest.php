<?php

namespace Tests\Unit;

use PhpArchiveStream\Writers\Tar\OutputStream;
use PhpArchiveStream\Writers\Tar\Tar;
use PHPUnit\Framework\TestCase;

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

        $this->tar = new Tar($this->outputPath);

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

    public function testConstructorAndStart()
    {
        $this->assertEquals($this->outputPath, $this->tar->outputPath);

        $reflection = new \ReflectionClass($this->tar);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $this->assertInstanceOf(OutputStream::class, $property->getValue($this->tar));
    }

    public function testAddMultipleFiles()
    {
        $this->tar->addFileFromPath('input1.txt', $this->inputPath1);
        $this->tar->addFileFromPath('input2.txt', $this->inputPath2);

        $reflection = new \ReflectionClass($this->tar);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $outputStream = $property->getValue($this->tar);

        $this->assertNotNull($outputStream);
    }

    public function testSave()
    {
        $reflection = new \ReflectionClass($this->tar);
        $property = $reflection->getProperty('outputStream');
        $property->setAccessible(true);
        $outputStream = $property->getValue($this->tar);

        $outputStreamClass = new \ReflectionClass($outputStream);
        $method = $outputStreamClass->getMethod('close');
        $method->setAccessible(true);
        $method->invoke($outputStream);

        $property->setValue($this->tar, null);
        $this->assertNull($property->getValue($this->tar));
    }

    public function testOutputFileExistsAfterSave()
    {
        $this->tar->addFileFromPath('input1.txt', $this->inputPath1);
        $this->tar->save();

        $this->assertFileExists($this->outputPath);
    }
}
