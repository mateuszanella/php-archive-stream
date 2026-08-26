<?php

declare(strict_types=1);

namespace Tests\Unit\IO;

use PhpArchiveStream\IO\Output\SpoolWriteStream;
use PHPUnit\Framework\TestCase;

class SpoolWriteStreamTest extends TestCase
{
    public function test_buffers_and_patches_data_before_flushing(): void
    {
        $inner = new BufferWriteStream;

        $spool = new SpoolWriteStream($inner);

        $spool->write(str_repeat("\0", 4));
        $spool->write('body');
        $spool->seek(0);
        $spool->write('head');
        $spool->close();

        $this->assertSame('headbody', $inner->buffer);
    }

    public function test_tracks_bytes_written(): void
    {
        $spool = new SpoolWriteStream(new BufferWriteStream);

        $spool->write('abc');
        $spool->write('def');

        $this->assertSame(6, $spool->getBytesWritten());
    }
}

class BufferWriteStream implements \PhpArchiveStream\Contracts\IO\WriteStream
{
    public string $buffer = '';

    protected int $bytesWritten = 0;

    public function write(string $s): int
    {
        $this->buffer .= $s;
        $this->bytesWritten += strlen($s);

        return strlen($s);
    }

    public function close(): void
    {
        //
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
