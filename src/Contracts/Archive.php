<?php

declare(strict_types=1);

namespace PhpArchiveStream\Contracts;

/**
 * @api
 */
interface Archive
{
    /**
     * Set the default read chunk size for file operations.
     *
     * @param  int  $chunkSize  The size in bytes for each read operation.
     */
    public function setDefaultReadChunkSize(int $chunkSize): static;

    /**
     * Add a file to the archive from a specified path.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $filePath  The path to the file on the filesystem.
     */
    public function addFileFromPath(string $fileName, string $filePath): static;

    /**
     * Add a file to the archive from a stream resource.
     *
     * The stream is read from its current position and is consumed by the
     * archive — ownership is transferred to the library, so the caller must
     * not `fclose()` it after invoking this method.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  resource  $stream  A valid, readable stream resource.
     */
    public function addFileFromStream(string $fileName, $stream): static;

    /**
     * Add a file to the archive from a string content.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $fileContents  The content of the file as a string.
     */
    public function addFileFromContentString(string $fileName, string $fileContents): static;

    /**
     * Finish the archive creation process.
     */
    public function finish(): static;
}
