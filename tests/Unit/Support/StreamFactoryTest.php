<?php

namespace Tests\Unit\Support;

use InvalidArgumentException;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\SpoolWriteStream;
use PhpArchiveStream\Support\StreamFactory;
use PHPUnit\Framework\TestCase;

class StreamFactoryTest extends TestCase
{
    public function test_uses_output_stream_for_seekable_destinations(): void
    {
        $stream = StreamFactory::make('7z', fopen('php://temp', 'w+b'));

        $this->assertInstanceOf(OutputStream::class, $stream);
    }

    public function test_spools_non_seekable_destinations(): void
    {
        $stream = StreamFactory::make('7z', fopen('php://output', 'wb'));

        $this->assertInstanceOf(SpoolWriteStream::class, $stream);
    }

    public function test_spool_strategy_forces_spooling_of_seekable_destinations(): void
    {
        $stream = StreamFactory::make('7z', fopen('php://temp', 'w+b'), [
            'streaming' => 'spool',
        ]);

        $this->assertInstanceOf(SpoolWriteStream::class, $stream);
    }

    public function test_seek_strategy_throws_on_non_seekable_destinations(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not seekable');

        StreamFactory::make('7z', fopen('php://output', 'wb'), [
            'streaming' => 'seek',
        ]);
    }
}
