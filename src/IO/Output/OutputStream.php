<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\SeekableWriteStream;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

class OutputStream implements SeekableWriteStream
{
    protected $stream;

    protected int $bytesWritten = 0;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function write(string $s): int
    {
        $bytesWritten = fwrite($this->stream, $s);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->bytesWritten += $bytesWritten;

        return $bytesWritten;
    }

    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->stream, $offset, $whence);
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
