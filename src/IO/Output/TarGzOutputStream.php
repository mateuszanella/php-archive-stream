<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

class TarGzOutputStream implements WriteStream
{
    protected $stream;

    protected int $bytesWritten = 0;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public static function open(string $path): self
    {
        $stream = gzopen($path, 'wb');

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return new self($stream);
    }

    public function close(): void
    {
        gzclose($this->stream);
    }

    public function write(string $s): void
    {
        $paddedData = $s;
        $paddingSize = 512 - (strlen($s) % 512);

        if ($paddingSize < 512) {
            $paddedData .= str_repeat("\0", $paddingSize);
        }

        $bytesWritten = gzwrite($this->stream, $paddedData);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
