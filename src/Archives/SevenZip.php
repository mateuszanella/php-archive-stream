<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class SevenZip implements Archive
{
    /**
     * Create a new SevenZip archive instance.
     *
     * @param  Writer|null  $writer  The writer instance to use for the archive.
     * @param  int  $defaultChunkSize  The default chunk size for reading files.
     */
    public function __construct(
        protected ?Writer $writer,
        protected int $defaultChunkSize = 4096,
    ) {}

    /**
     * Set the current compression algorithm for the archive.
     *
     * @param  string  $compressor  The compressor to set as default.
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
     */
    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $stream = InputStream::open($filePath, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Add a file to the archive from a given stream.
     *
     * @param  resource  $stream  The stream resource to read from.
     */
    public function addFileFromStream(string $fileName, $stream): void
    {
        $stream = InputStream::fromStream($stream, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Add a file to the archive from a string content.
     */
    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $stream = InputStream::fromString($fileContents, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    /**
     * Finish the archive and close the writer.
     */
    public function finish(): void
    {
        $this->writer->finish();
        $this->writer = null;
    }
}
