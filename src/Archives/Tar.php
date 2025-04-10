<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\IO\Input\InputStream;

class Tar implements Archive
{
    public function __construct(
        protected ?Writer $writer,
        protected int $defaultChunkSize = 512,
    ) {}

    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $stream = InputStream::open($filePath, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromStream(string $fileName, $stream): void
    {
        $stream = InputStream::fromStream($stream, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $stream = InputStream::fromString($fileContents, $this->defaultChunkSize);

        $this->writer->addFile($stream, $fileName);
    }

    public function finish(): void
    {
        $this->writer->finish();
        $this->writer = null;
    }
}
