<?php

namespace PhpArchiveStream\Writers\Zip;

use PhpArchiveStream\Contracts\ReadStream;
use PhpArchiveStream\Writers\Tar\IO\OutputStream;

class ZipWriter
{
    protected ?OutputStream $outputStream;

    public function __construct(string $outputPath)
    {
        $this->outputStream = OutputStream::open($outputPath);
    }

    public function create(string $outputPath): self
    {
        return new self($outputPath);
    }

    public function addFile(ReadStream $stream, string $fileName): void
    {
        // Add local header

        // Add file data

        // Add data descriptor
    }

    public function finish(): void
    {
        // Add central directory

        // Add end of central directory
    }
}
