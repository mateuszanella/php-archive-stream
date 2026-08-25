<?php

declare(strict_types=1);

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class Tar implements Archive
{
    /**
     * Create a new Tar archive instance.
     *
     * @param  Writer|null  $writer  The writer instance to use for the archive.
     * @param  int  $defaultChunkSize  The default chunk size for reading files.
     */
    public function __construct(
        protected ?Writer $writer,
        protected int $defaultChunkSize = 512,
    ) {}

    /**
     * Set the default read chunk size in bytes for files added to the archive.
     */
    public function setDefaultReadChunkSize(int $chunkSize): static
    {
        $this->defaultChunkSize = $chunkSize;

        return $this;
    }

    /**
     * Add a file to the archive from a given path.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $filePath  The path to the file to add.
     */
    public function addFileFromPath(string $fileName, string $filePath): static
    {
        $stream = InputStream::open($filePath, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);

        return $this;
    }

    /**
     * Add a file to the archive from a given stream.
     *
     * The stream is read from its current position and is consumed by the
     * archive — ownership is transferred to the library, so the caller must
     * not `fclose()` it after invoking this method.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  resource  $stream  The stream resource to read from.
     */
    public function addFileFromStream(string $fileName, $stream): static
    {
        $stream = InputStream::fromStream($stream, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);

        return $this;
    }

    /**
     * Add a file to the archive from a string content.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $fileContents  The contents of the file to add.
     */
    public function addFileFromContentString(string $fileName, string $fileContents): static
    {
        $stream = InputStream::fromString($fileContents, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);

        return $this;
    }

    /**
     * Finish the archive and close the writer. The class should not be used after this call.
     */
    public function finish(): static
    {
        $this->writer->finish();
        $this->writer = null;

        return $this;
    }
}
