<?php

namespace PhpArchiveStream\Writers\Tar;

use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\Writers\Writer;

class TarWriter implements Writer
{
    protected ?WriteStream $outputStream;

    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;
    }

    public function addFile(ReadStream $stream, string $fileName): void
    {
        $sourceFileSize = $stream->size();

        $this->writeHeaderBlock($fileName, $sourceFileSize);

        $this->writeFileDataBlock($stream);

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

    protected function writeFileDataBlock(ReadStream $inputStream): void
    {
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
