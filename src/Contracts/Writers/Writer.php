<?php

declare(strict_types=1);

namespace PhpArchiveStream\Contracts\Writers;

use PhpArchiveStream\Contracts\IO\ReadStream;

interface Writer
{
    /**
     * Add a file to the archive.
     *
     * @param  ReadStream  $stream  The stream containing the file data.
     * @param  string  $fileName  The name of the file in the archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void;

    /**
     * Set the default compressor used for files added to the archive.
     *
     * @param  string  $compressor  The fully qualified class name of the compressor.
     * @param  array<string, mixed>  $options  Options forwarded to the compressor's `init()` factory.
     */
    public function setDefaultCompressor(string $compressor, array $options = []): void;

    /**
     * Finish the writing process and close the archive.
     */
    public function finish(): void;
}
