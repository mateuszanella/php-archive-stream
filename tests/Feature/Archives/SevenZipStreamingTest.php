<?php

namespace Tests\Feature\Archives;

use PhpArchiveStream\ArchiveManager;
use PHPUnit\Framework\TestCase;

class SevenZipStreamingTest extends TestCase
{
    protected string $outputPath = './streaming.7z';

    protected string $spoolPath = './spooled.7z';

    protected string $inputPath = './input.txt';

    protected function setUp(): void
    {
        if (! function_exists('xz_encode_init')) {
            $this->markTestSkipped('The xz extension is not available.');
        }

        foreach ([$this->outputPath, $this->spoolPath, $this->inputPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        file_put_contents($this->inputPath, str_repeat('Hello World ', 1000));
    }

    protected function tearDown(): void
    {
        foreach ([$this->outputPath, $this->spoolPath, $this->inputPath] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function test_streaming_and_spool_strategies_produce_identical_archives(): void
    {
        $streaming = ArchiveManager::make()->create($this->outputPath);
        $streaming->addFileFromPath('input.txt', $this->inputPath);
        $streaming->finish();

        $spooling = ArchiveManager::make([
            '7z' => ['streaming' => 'spool'],
        ])->create($this->spoolPath);
        $spooling->addFileFromPath('input.txt', $this->inputPath);
        $spooling->finish();

        $this->assertSame(
            file_get_contents($this->outputPath),
            file_get_contents($this->spoolPath),
        );
    }
}
