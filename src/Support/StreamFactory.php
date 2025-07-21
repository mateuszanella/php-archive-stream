<?php
namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\IO\Output\GzOutputStream;
use PhpArchiveStream\IO\Output\OutputStream;

class StreamFactory implements StreamFactoryContract
{
    /**
     * Create a stream based on the provided extension and stream resource.
     *
     * @param string $extension The file extension to determine the type of stream.
     * @param resource $stream The stream resource to wrap.
     * @return WriteStream
     * @throws InvalidArgumentException If the extension is not supported.
     */
    public static function make(string $extension, $stream): WriteStream
    {
        return match ($extension) {
            'zip'    => new OutputStream($stream),
            'tar'    => new OutputStream($stream),
            'tar.gz' => new GzOutputStream($stream),
            default  => throw new InvalidArgumentException("Unsupported destination type: {$extension}"),
        };
    }
}
