<?php

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

class ArrayOutputStream implements WriteStream
{
    protected array $streams = [];

    protected int $bytesWritten = 0;

    public function __construct(array $streams)
    {
        $this->streams = $streams;
    }

    public function close(): void
    {
        foreach ($this->streams as $stream) {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function write(string $s): int
    {
        $totalBytesWritten = 0;

        foreach ($this->streams as $stream) {
            $bytesWritten = $stream->write($s);

            $totalBytesWritten += $bytesWritten;
        }

        $this->bytesWritten += $totalBytesWritten;

        return $totalBytesWritten;
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }
}
