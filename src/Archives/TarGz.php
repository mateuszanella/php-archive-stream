<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\TarGz\IO\InputStream;
use PhpArchiveStream\Writers\TarGz\TarGzWriter;

class TarGz implements Archive
{
    protected ?TarWriter $writer;

    public function __construct(
        TarWriter $writer
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
