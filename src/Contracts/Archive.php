<?php

namespace PhpArchiveStream\Contracts;

interface Archive
{
    /**
     * Set the default read chunk size for file operations.
     *
     * @param  int  $chunkSize  The size in bytes for each read operation.
     */
    public function setDefaultReadChunkSize(int $chunkSize): void;

    /**
     * Add a file to the archive from a specified path.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $filePath  The path to the file on the filesystem.
     */
    public function addFileFromPath(string $fileName, string $filePath): void;

    /**
     * Add a file to the archive from a stream resource.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  resource  $stream  A valid stream resource.
     */
    public function addFileFromStream(string $fileName, $stream): void;

    /**
     * Add a file to the archive from a string content.
     *
     * @param  string  $fileName  The name of the file in the archive.
     * @param  string  $fileContents  The content of the file as a string.
     */
    public function addFileFromContentString(string $fileName, string $fileContents): void;

    /**
     * Finish the archive creation process.
     */
    public function finish(): void;
}
