<?php

namespace PhpArchiveStream\Archives;

use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Writers\Tar\IO\InputStream;
use PhpArchiveStream\Writers\Tar\TarWriter;

class Tar implements Archive
{
    public readonly string $outputPath;

    protected ?TarWriter $writer;

    public function __construct(
        string $outputPath,
        TarWriter $writer
    ) {
        $this->outputPath = $outputPath;
        $this->writer = $writer;
    }

    public static function create(string $outputPath): self
    {
        $writer = TarWriter::create($outputPath);

        return new self($outputPath, $writer);
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
