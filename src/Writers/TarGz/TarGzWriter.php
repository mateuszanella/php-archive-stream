<?php

namespace PhpArchiveStream\Writers\TarGz;

use PhpArchiveStream\Contracts\ReadStream;
use PhpArchiveStream\Writers\TarGz\IO\InputStream;
use PhpArchiveStream\Writers\TarGz\IO\OutputStream;

class TarGzWriter
{
    protected ?OutputStream $outputStream;

    public function __construct(string $outputPath)
    {
        $this->outputStream = OutputStream::open($outputPath);
    }

    public static function create(string $outputPath): self
    {
        return new self($outputPath);
    }

    public function addFile(ReadStream $stream, string $fileName): void
    {
        $this->writeFileDataBlock($stream, $fileName);

        $stream->close();
    }

    public function finish(): void
    {
        $this->writeTrailerBlock();

        if ($this->outputStream) {
            $this->outputStream->close();
            $this->outputStream = null;
        }
    }

    protected function writeFileDataBlock(InputStream $inputStream, string $outputFilePath): void
    {
        $sourceFileSize = $inputStream->size();

        $this->writeHeaderBlock($outputFilePath, $sourceFileSize);

        foreach ($inputStream->read() as $chunk) {
            $this->outputStream->write($chunk);
        }
    }

    protected function writeHeaderBlock(string $outputFilePath, int $sourceFileSize): void
    {
        $baseFileName = basename($outputFilePath);
        $folderPrefix = dirname($outputFilePath);

        $header = Header::generate(
            $baseFileName,
            $folderPrefix,
            $sourceFileSize
        );

        $this->outputStream->write($header);
    }

    protected function writeTrailerBlock(): void
    {
        $this->outputStream->write(str_repeat("\0", 1024));
    }
}
