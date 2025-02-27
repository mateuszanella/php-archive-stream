<?php

namespace LaravelFileStream\Writers\Tar;

use LaravelFileStream\Exceptions\CouldNotOpenStreamException;
use LaravelFileStream\Exceptions\CouldNotWriteToStreamException;

class InputStream
{
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public static function open(string $path): self
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return new self($stream);
    }

    public function write(string $s): void
    {
        $bytesWritten = fwrite($this->stream, $s);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->pad($bytesWritten);
    }

    public function pad(int $bytesWritten): void
    {
        $remainder = $bytesWritten % 512;
        if ($remainder) {
            $padding = 512 - $remainder;
            $bytesWritten = fwrite($this->stream, str_repeat("\0", $padding));

            if ($bytesWritten === false) {
                throw new CouldNotWriteToStreamException;
            }
        }
    }
}
