<?php

namespace LaravelFileStream\Writers\Tar;

use LaravelFileStream\Writers\Writer;

class Tar implements Writer
{
    public readonly string $outputPath;

    protected ?OutputStream $outputStream;

    public function __construct(string $path)
    {
        $this->outputPath = $path;

        $this->start($path);
    }

    public function addFile(string $path): void
    {
        $inputStream = InputStream::open($path);

        $this->writeFileDataBlock($inputStream, $path);

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
        $this->writeHeaderBlock($outputFilePath);

        foreach ($inputStream->read() as $chunk) {
            $this->write($chunk);
        }
    }

    protected function writeHeaderBlock(string $outputFilePath): void
    {
        $baseFileName = basename($outputFilePath);
        $folderPrefix = dirname($outputFilePath);
        $fileSize = filesize($outputFilePath);

        $header = Header::get(
            $baseFileName,
            $folderPrefix,
            $fileSize
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
