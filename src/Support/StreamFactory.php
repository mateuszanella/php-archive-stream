<?php

namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\IO\Output\GzOutputStream;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\SpoolWriteStream;

class StreamFactory implements StreamFactoryContract
{
    /**
     * Create a stream based on the provided extension and stream resource.
     *
     * @param  string  $extension  The file extension to determine the type of stream.
     * @param  resource  $stream  The stream resource to wrap.
     * @param  array<string, mixed>  $config  Configuration options for the stream type.
     *
     * @throws InvalidArgumentException If the extension is not supported.
     */
    public static function make(string $extension, $stream, array $config = []): WriteStream
    {
        return match ($extension) {
            'zip'    => new OutputStream($stream),
            'tar'    => new OutputStream($stream),
            'tar.gz' => new GzOutputStream($stream),
            '7z'     => static::makeSevenZip($stream, $config),
            default  => throw new InvalidArgumentException("Unsupported destination type: {$extension}"),
        };
    }

    /**
     * Create the output stream for a 7z archive.
     *
     * The 7z writer requires a seekable output stream (see `SevenZipWriter`).
     * Non-seekable destinations are therefore wrapped in a `SpoolWriteStream`,
     * which buffers the archive and provides the required seeking capability.
     *
     * The `streaming` configuration option controls this behavior:
     * - `auto` (default): wrap non-seekable destinations, leave seekable ones untouched;
     * - `seek`: require a seekable destination, throwing otherwise;
     * - `spool`: always wrap the destination, regardless of seekability.
     *
     * @param  resource  $stream  The stream resource to wrap.
     * @param  array<string, mixed>  $config  Configuration options for the stream type.
     *
     * @throws InvalidArgumentException If the `seek` strategy is forced on a non-seekable stream.
     */
    protected static function makeSevenZip($stream, array $config): WriteStream
    {
        $strategy = $config['streaming'] ?? 'auto';
        $seekable = (bool) (stream_get_meta_data($stream)['seekable'] ?? false);

        if ($strategy === 'seek' && ! $seekable) {
            throw new InvalidArgumentException('Cannot stream 7z archive: the destination stream is not seekable.');
        }

        if ($strategy === 'spool' || ! $seekable) {
            return new SpoolWriteStream(new OutputStream($stream));
        }

        return new OutputStream($stream);
    }
}
