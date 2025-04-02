<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Writers\Zip\IO\InputStream;
use PhpArchiveStream\Writers\Zip\Zip64Writer;

class Zip implements Archive
{
    protected ?Zip64Writer $writer;

    public function __construct(
        Zip64Writer $writer
    ) {
        $this->writer = $writer;
    }

    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $stream = InputStream::open($filePath);

        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromStream(string $fileName, $stream): void
    {
        $stream = InputStream::fromStream($stream);

        $this->writer->addFile($stream, $fileName);
    }

    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $stream = InputStream::fromString($fileContents);

        $this->writer->addFile($stream, $fileName);
    }

    public function finish(): void
    {
        $this->writer->finish();
        $this->writer = null;
    }
}
