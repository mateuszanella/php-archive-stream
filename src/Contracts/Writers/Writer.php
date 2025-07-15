<?php

namespace PhpArchiveStream\Contracts\Writers;

use PhpArchiveStream\Contracts\IO\ReadStream;

interface Writer
{
    /**
     * Add a file to the archive.
     *
     * @param  ReadStream  $stream The stream containing the file data.
     * @param  string  $fileName The name of the file in the archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void;

    /**
     * Finish the writing process and close the archive.
     */
    public function finish(): void;
}
