<?php

namespace PhpArchiveStream\Contracts\IO;

/**
 * A write stream that supports repositioning the write pointer.
 *
 * Some archive formats (such as 7z) must patch bytes that were written before
 * the data they describe. This capability is exposed as an optional interface
 * rather than part of {@see WriteStream} so that non-seekable destinations
 * (`php://output`, cloud wrappers, custom streams) remain valid `WriteStream`
 * implementations.
 *
 * Writers that require seeking type-hint this interface and rely on the stream
 * layer to provide it — either natively or via a buffering decorator such as
 * `PhpArchiveStream\IO\Output\SpoolWriteStream`.
 */
interface SeekableWriteStream extends WriteStream
{
    /**
     * Move the write pointer to the given offset.
     *
     * @param  int  $offset  The offset to seek to.
     * @param  int  $whence  The reference point for the offset.
     * @return int Zero on success, or -1 on failure.
     */
    public function seek(int $offset, int $whence = SEEK_SET): int;
}
