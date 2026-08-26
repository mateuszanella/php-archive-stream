<?php

declare(strict_types=1);

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

/**
 * @internal
 */
class GzOutputStream implements WriteStream
{
    /**
     * @var resource
     */
    protected $stream;

    protected int $bytesWritten = 0;

    /**
     * @param  resource  $stream  A gzip stream opened with `gzopen()`.
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function close(): void
    {
        gzclose($this->stream);
    }

    public function write(string $s): int
    {
        $bytesWritten = gzwrite($this->stream, $s);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->bytesWritten += $bytesWritten;

        return $bytesWritten;
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
