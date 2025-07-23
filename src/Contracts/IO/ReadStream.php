<?php

namespace PhpArchiveStream\Contracts\IO;

use Generator;

interface ReadStream
{
    /**
     * Create a new ReadStream instance from a file path.
     *
     * @param  string  $path  The path to the file.
     * @param  int  $chunkSize  The size of each chunk to read.
     */
    public static function open(string $path, int $chunkSize): self;

    /**
     * Create a new ReadStream instance from a stream resource.
     *
     * @param  resource  $stream  The stream resource.
     * @param  int  $chunkSize  The size of each chunk to read.
     */
    public static function fromStream($stream, int $chunkSize): self;

    /**
     * Create a new ReadStream instance from a content string.
     *
     * @param  string  $contents  The string content.
     * @param  int  $chunkSize  The size of each chunk to read.
     */
    public static function fromString(string $contents, int $chunkSize): self;

    /**
     * Close the stream.
     */
    public function close(): void;

    /**
     * Read data from the stream.
     *
     * @return Generator<string> Yields chunks of data from the stream.
     */
    public function read(): Generator;

    /**
     * Get the size of the stream.
     *
     * @return int The size of the stream in bytes.
     */
    public function size(): int;
}
