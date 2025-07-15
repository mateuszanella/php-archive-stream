<?php

namespace PhpArchiveStream\Contracts\IO;

interface WriteStream
{
    /**
     * Close the stream.
     */
    public function close(): void;

    /**
     * Write data to the stream.
     *
     * @param  string  $s The data to write.
     * @return int The number of bytes written.
     */
    public function write(string $s): int;

    /**
     * Get the number of bytes written to the stream.
     */
    public function getBytesWritten(): int;
}
