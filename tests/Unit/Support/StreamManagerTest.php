<?php

namespace Tests\Unit\Support;

use InvalidArgumentException;
use PhpArchiveStream\IO\Output\GzOutputStream;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\SpoolWriteStream;
use PhpArchiveStream\StreamManager;
use PHPUnit\Framework\TestCase;

class StreamManagerTest extends TestCase
{
    protected string $path = './stream-manager-test.txt';

    protected function tearDown(): void
    {
        foreach ([$this->path, $this->path.'.gz'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function test_uses_output_stream_for_seekable_destinations(): void
    {
        $stream = (new StreamManager)->make('7z', 'php://temp');

        $this->assertInstanceOf(OutputStream::class, $stream);
    }

    public function test_spools_non_seekable_destinations(): void
    {
        $stream = (new StreamManager)->make('7z', 'php://output');

        $this->assertInstanceOf(SpoolWriteStream::class, $stream);
    }

    public function test_spool_strategy_forces_spooling_of_seekable_destinations(): void
    {
        $stream = (new StreamManager)->make('7z', 'php://temp', [
            'streaming' => 'spool',
        ]);

        $this->assertInstanceOf(SpoolWriteStream::class, $stream);
    }

    public function test_seek_strategy_throws_on_non_seekable_destinations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not seekable');

        (new StreamManager)->make('7z', 'php://output', [
            'streaming' => 'seek',
        ]);
    }

    public function test_uses_gzip_stream_for_tar_gz(): void
    {
        $stream = (new StreamManager)->make('tar.gz', 'php://temp');

        $this->assertInstanceOf(GzOutputStream::class, $stream);
    }

    public function test_throws_on_unknown_extension(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new StreamManager)->make('unknown', 'php://temp');
    }

    public function test_allows_registering_custom_stream_builders(): void
    {
        $manager = new StreamManager;

        $manager->register('custom', fn (string $destination) => new OutputStream(fopen($destination, 'wb')));

        $stream = $manager->make('custom', $this->path);

        $this->assertInstanceOf(OutputStream::class, $stream);
        $this->assertFileExists($this->path);
    }
}
