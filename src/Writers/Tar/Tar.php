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

    public function addFileFromPath(string $filePath, string $targetPath): void
    {
        $inputStream = InputStream::open($filePath);

        $this->writeFileDataBlock($inputStream, $targetPath);

        $inputStream->close();
    }

    public function addFile(string $fileName, string $fileContents): void
    {
        $inputStream = InputStream::fromString($fileContents);

        $this->writeFileDataBlock($inputStream, $fileName);
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
