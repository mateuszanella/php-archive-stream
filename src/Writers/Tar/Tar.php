<?php

namespace PhpArchiveStream\Writers\Tar;

use PhpArchiveStream\Writers\Writer;

class Tar implements Writer
{
    public readonly string $outputPath;

    protected ?OutputStream $outputStream;

    public function __construct(string $path)
    {
        $this->outputPath = $path;

        $this->start($path);
    }

    public function addFileFromPath(string $fileName, string $filePath): void
    {
        $inputStream = InputStream::open($filePath);

        $this->writeFileDataBlock($inputStream, $fileName);

        $inputStream->close();
    }

    public function addFileFromStream(string $fileName, $stream): void
    {
        $inputStream = InputStream::fromStream($stream);

        $this->writeFileDataBlock($inputStream, $fileName);

        $inputStream->close();
    }

    public function addFileFromContentString(string $fileName, string $fileContents): void
    {
        $inputStream = InputStream::fromString($fileContents);

        $this->writeFileDataBlock($inputStream, $fileName);

        $inputStream->close();
    }

    public function save(): void
    {
        $this->writeTrailerBlock();

        if ($this->outputStream) {
            $this->outputStream->close();
            $this->outputStream = null;
        }
    }

    protected function start(string $outputPath): void
    {
        $this->outputStream = OutputStream::open($outputPath);
    }

    protected function writeFileDataBlock(InputStream $inputStream, string $outputFilePath): void
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

        $header = Header::get(
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

    protected function write(string $data): void
    {
        $this->outputStream->write($data);
    }
}
