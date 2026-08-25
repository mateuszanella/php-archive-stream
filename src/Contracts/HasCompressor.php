<?php

namespace PhpArchiveStream\Contracts;

interface HasCompressor
{
    /**
     * Set the default compressor used for files added to the archive.
     *
     * @param  string  $compressor  The fully qualified class name of the compressor.
     * @param  array<string, mixed>  $options  Options forwarded to the compressor's `init()` factory.
     */
    public function setDefaultCompressor(string $compressor, array $options = []): void;
}
