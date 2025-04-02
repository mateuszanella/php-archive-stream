<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

class OutputStream implements WriteStream
{
    protected $stream;

    protected int $bytesWritten = 0;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public static function open(string $path): self
    {
        $stream = fopen($path, 'wb');

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return new self($stream);
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function write(string $s): void
    {
        $bytesWritten = fwrite($this->stream, $s);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->bytesWritten += $bytesWritten;
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
