<?php

namespace PhpArchiveStream\Writers\Tar;

use PhpArchiveStream\Contracts\ReadStream;
use PhpArchiveStream\Contracts\WriteStream;

class TarWriter
{
    protected ?WriteStream $outputStream;

    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;
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

    protected function writeFileDataBlock(ReadStream $inputStream, string $outputFilePath): void
    {
        $sourceFileSize = $inputStream->size();

        $this->writeHeaderBlock($outputFilePath, $sourceFileSize);

        foreach ($inputStream->read() as $chunk) {
            $this->write($chunk);
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

        $this->write($header);
    }

    protected function writeTrailerBlock(): void
    {
        $this->write(str_repeat("\0", 1024));
    }

    private function write(string $data): void
    {
        $this->outputStream->write($data);
    }
}
