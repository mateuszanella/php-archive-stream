<?php

namespace LaravelFileStream\Writers\Tar;

use Exception;
use LaravelFileStream\Writers\Writer;

class Tar implements Writer
{
    public readonly string $outputPath;

    protected $outputStream;

    public function __construct(string $path)
    {
        $this->outputPath = $path;

        $this->start($path);
    }

    public function addFile(string $path): void {}

    public function save(): void {}

    /**
     * Everything from down here should be a different Stream class.
     *
     * A TarStream should be resposible for reading, writing and padding the tar file.
     *
     * The Tar file should be responsible for generating headers, adding files and saving the file itself.
     */


    protected function start(string $outputPath): void
    {
        $this->outputStream = fopen($outputPath, 'wb');

        if ($this->outputStream === false) {
            throw new Exception('Could not open file for writing');
        }
    }

    protected function writeFileDataBlock($inputStream, string $outputFilePath): void
    {
        $this->writeHeaderBlock($outputFilePath);

        while (!feof($inputStream)) {
            $data = fread($inputStream, 512);

            if ($data === false) {
                throw new Exception('Could not read from input stream');
            }

            $this->writeChunk($data);
        }

        $this->writeTrailerBlock();
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

        $this->writeChunks($header);
    }

    protected function writeChunks(string $data): void
    {
        $length = strlen($data);

        for ($offset = 0; $offset < $length; $offset += 512) {
            $chunk = substr($data, $offset, 512);
            $this->write($chunk);
        }
    }

    protected function pad(int $size): void
    {
        $paddingSize = 512 - ($size % 512);

        fwrite($this->outputStream, str_repeat("\0", $paddingSize));
    }

    protected function write(string $data): void
    {
        fwrite($this->outputStream, $data);

        if (strlen($data) < 512) {
            $this->pad(strlen($data));
        }
    }
}
