<?php

namespace PhpArchiveStream\Contracts;

use PhpArchiveStream\Contracts\IO\WriteStream;

interface StreamFactory
{
    /**
     * Create a stream based on the provided extension and stream resource.
     *
     * @param string $extension The file extension to determine the type of stream.
     * @param resource $stream The stream resource to wrap.
     * @return WriteStream
     * @throws InvalidArgumentException If the extension is not supported.
     */
    public static function make(string $extension, $stream): WriteStream;
}
