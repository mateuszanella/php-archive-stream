<?php

namespace PhpArchiveStream\IO\Output;

use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\WriteStream;

class ArrayOutputStream implements WriteStream
{
    /**
     * The array of streams to write to.
     *
     * @var array<\PhpArchiveStream\Contracts\IO\WriteStream>
     */
    protected array $streams = [];

    /**
     * The unit amount of bytes written for each stream.
     */
    protected int $bytesWritten = 0;

    /**
     * Create a new ArrayOutputStream instance.
     *
     * @param  array<\PhpArchiveStream\Contracts\IO\WriteStream>  $streams
     */
    public function __construct(array $streams)
    {
        $this->validate($streams);

        $this->streams = $streams;
    }

    /**
     * Close all streams.
     */
    public function close(): void
    {
        foreach ($this->streams as $stream) {
            $stream->close();
        }
    }

    /**
     * Write a string to all streams.
     */
    public function write(string $s): int
    {
        foreach ($this->streams as $stream) {
            $stream->write($s);
        }

        // Although this is not the best solution, we must ensure
        // that the bytes written variable contains a correct value.
        return $this->bytesWritten = $this->streams[0]->getBytesWritten();
    }

    /**
     * Get the total number of bytes written to a stream.
     */
    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }

    /**
     * Validate the passed objects to ensure they are instances of WriteStream.
     *
     * @param  array<\PhpArchiveStream\Contracts\IO\WriteStream>  $streams
     */
    protected function validate(array $streams): void
    {
        if (empty($streams)) {
            throw new InvalidArgumentException('The streams array cannot be empty.');
        }

        foreach ($streams as $stream) {
            if (! $stream instanceof WriteStream) {
                throw new InvalidArgumentException('The stream must implement the WriteStream interface.');
            }
        }
    }
}
