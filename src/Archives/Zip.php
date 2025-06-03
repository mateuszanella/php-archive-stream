<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class Zip implements Archive
{
    /**
     * Create a new Zip archive instance.
     *
     * @param  Writer|null  $writer The writer instance to use for the archive.
     * @param  int  $defaultChunkSize The default chunk size for reading files.
     */
    public function __construct(
        protected ?Writer $writer,
        protected int $defaultChunkSize = 4096,
    ) {}

    /**
     * Set the current compression algorithm for the archive.
     *
     * Usage:
     * ```php
     * $zip->setDefaultCompressor(DeflateCompressor::class);
     * ```
     *
     * @param  string  $compressor The compressor to set as default.
     */
    public function setDefaultCompressor(string $compressor): void
    {
        $this->writer->setDefaultCompressor($compressor);
    }

    /**
     * Set the default read chunk size in bytes for files added to the archive.
     */
    public function setDefaultReadChunkSize(int $chunkSize): void
    {
        $this->defaultChunkSize = $chunkSize;
    }

    /**
     * Add a file to the archive from a given path.
     *
     * @param  string  $fileName The name of the file in the archive.
     * @param  string  $filePath The path to the file to add.
     */
    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $stream = InputStream::open($filePath, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Add a file to the archive from a given stream.
     *
     * @param  string  $fileName The name of the file in the archive.
     * @param  resource  $stream The stream resource to read from.
     */
    public function addFileFromStream(string $fileName, $stream): void
    {
        $stream = InputStream::fromStream($stream, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Add a file to the archive from a string content.
     *
     * @param  string  $fileName The name of the file in the archive.
     * @param  string  $fileContents The contents of the file to add.
     */
    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $stream = InputStream::fromString($fileContents, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Finish the archive and close the writer. The class should not be used after this call.
     */
    public function finish(): void
    {
        $this->writer->finish();
        $this->writer = null;
    }
}
