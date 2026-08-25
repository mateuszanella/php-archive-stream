<?php

declare(strict_types=1);

namespace Tests\Feature\Archives;

use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\IO\Output\Bz2OutputStream;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \PhpArchiveStream\Archives\Tar
 */
class TarBz2Test extends TestCase
{
    protected string $outputPath = './output.tar.bz2';

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
        if (! function_exists('bzopen')) {
            $this->markTestSkipped('The bz2 extension is required for this test');
        }

        $stream = bzopen($this->outputPath, 'w');
        $outputStream = new Bz2OutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->addFileFromPath('input1.txt', $this->inputPath1);
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_stream()
    {
        if (! function_exists('bzopen')) {
            $this->markTestSkipped('The bz2 extension is required for this test');
        }

        $stream = bzopen($this->outputPath, 'w');
        $outputStream = new Bz2OutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $resource = fopen($this->inputPath1, 'r');
        $tar->addFileFromStream('input1.txt', $resource);
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_add_file_from_content_string()
    {
        if (! function_exists('bzopen')) {
            $this->markTestSkipped('The bz2 extension is required for this test');
        }

        $stream = bzopen($this->outputPath, 'w');
        $outputStream = new Bz2OutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->addFileFromContentString('input1.txt', 'Hello World 1');
        $tar->finish();

        $this->assertFileExists($this->outputPath);
    }

    public function test_finish()
    {
        if (! function_exists('bzopen')) {
            $this->markTestSkipped('The bz2 extension is required for this test');
        }

        $stream = bzopen($this->outputPath, 'w');
        $outputStream = new Bz2OutputStream($stream);
        $tarWriter = new TarWriter($outputStream);
        $tar = new Tar($tarWriter);

        $tar->finish();

        $reflection = new ReflectionClass($tar);
        $property = $reflection->getProperty('writer');
        $this->assertNull($property->getValue($tar));
    }
}
