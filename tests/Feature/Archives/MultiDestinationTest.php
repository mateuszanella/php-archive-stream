<?php

namespace Tests\Feature\Archives;

use PhpArchiveStream\ArchiveManager;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\Writers\Zip\Zip64Writer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhpArchiveStream\Archives\Zip
 */
class MultiDestinationTest extends TestCase
{
    protected string $outputPath1 = './output1.zip';
    protected string $outputPath2 = './output2.zip';

    protected string $inputPath1 = './input1.txt';
    protected string $inputPath2 = './input2.txt';

    protected ArchiveManager $manager;

    protected function setUp(): void
    {
        $this->manager = new ArchiveManager;

        if (file_exists($this->outputPath1)) {
            unlink($this->outputPath1);
        }

        if (file_exists($this->outputPath2)) {
            unlink($this->outputPath2);
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
        if (file_exists($this->outputPath1)) {
            unlink($this->outputPath1);
        }

        if (file_exists($this->outputPath2)) {
            unlink($this->outputPath2);
        }

        if (file_exists($this->inputPath1)) {
            unlink($this->inputPath1);
        }

        if (file_exists($this->inputPath2)) {
            unlink($this->inputPath2);
        }
    }

    public function testMultiDestinationZipFile()
    {
        $zip = $this->manager->create([
            $this->outputPath1,
            $this->outputPath2,
        ]);

        $zip->addFileFromPath('input1.txt', $this->inputPath1);
        $zip->addFileFromPath('input2.txt', $this->inputPath1);

        $zip->finish();

        $this->assertFileExists($this->outputPath1);
        $this->assertFileExists($this->outputPath2);
    }
}
