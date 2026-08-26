<?php

declare(strict_types=1);

namespace PhpArchiveStream\IO\Output;

use PhpArchiveStream\Contracts\IO\SeekableWriteStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\Exceptions\CouldNotWriteToStreamException;

/**
 * A decorator that makes a non-seekable destination seekable by buffering.
 *
 * All written data is buffered in a `php://temp` handle (memory up to a
 * threshold, then disk), which is itself seekable. Seeking therefore operates
 * against the buffer, and the buffered bytes are flushed to the wrapped stream
 * when the stream is closed.
 *
 * This allows writers that require seeking (such as the 7z writer, which
 * patches a signature header written before the archive payload) to target
 * non-seekable destinations like `php://output` or cloud wrappers.
 *
 * @internal
 */
class SpoolWriteStream implements SeekableWriteStream
{
    /**
     * The wrapped, non-seekable destination stream.
     */
    protected WriteStream $stream;

    /**
     * The seekable buffer where written data is spooled.
     *
     * @var resource
     */
    protected $spool;

    /**
     * The number of bytes written to the spool.
     */
    protected int $bytesWritten = 0;

    /**
     * Create a new SpoolWriteStream instance.
     *
     * @param  WriteStream  $stream  The destination stream to flush buffered data to.
     */
    public function __construct(WriteStream $stream)
    {
        $this->stream = $stream;

        $spool = fopen('php://temp', 'w+b');

        if ($spool === false) {
            throw new CouldNotOpenStreamException('php://temp');
        }

        $this->spool = $spool;
    }

    public function write(string $s): int
    {
        $bytesWritten = fwrite($this->spool, $s);
        if ($bytesWritten === false) {
            throw new CouldNotWriteToStreamException;
        }

        $this->bytesWritten += $bytesWritten;

        return $bytesWritten;
    }

    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->spool, $offset, $whence);
    }

    public function getBytesWritten(): int
    {
        return $this->bytesWritten;
    }

    /**
     * Flush the buffered data to the wrapped stream and close it.
     */
    public function close(): void
    {
        rewind($this->spool);

        while (! feof($this->spool)) {
            $chunk = fread($this->spool, 1048576);

            if ($chunk === false) {
                break;
            }

            $this->stream->write($chunk);
        }

        fclose($this->spool);
        $this->stream->close();
    }
}
