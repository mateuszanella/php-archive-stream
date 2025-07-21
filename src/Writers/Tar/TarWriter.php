<?php

namespace PhpArchiveStream\Writers\Tar;

use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\Writers\Writer;

class TarWriter implements Writer
{
    /**
     * The output stream where the TAR archive will be written.
     */
    protected ?WriteStream $outputStream;

    /**
     * Create a new TarWriter instance.
     */
    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;
    }

    /**
     * Add a file to the TAR archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void
    {
        $sourceFileSize = $stream->size();

        $this->writeHeaderBlock($fileName, $sourceFileSize);

        $this->writeFileDataBlock($stream);
    }

    /**
     * Finish writing the TAR archive.
     */
    public function finish(): void
    {
        $this->writeTrailerBlock();

        $this->outputStream->close();
        $this->outputStream = null;
    }

    /**
     * Write the file data block to the TAR archive.
     */
    protected function writeFileDataBlock(ReadStream $inputStream): void
    {
        $bytesWritten = 0;

        foreach ($inputStream->read() as $chunk) {
            $bytesWritten += $this->outputStream->write($chunk);
        }

        if ($bytesWritten % 512 !== 0) {
            $paddingSize = 512 - ($bytesWritten % 512);

            $this->outputStream->write(str_repeat("\0", $paddingSize));
        }
    }

    /**
     * Write the header block for a file in the TAR archive.
     */
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

    /**
     * Write the trailer block to the TAR archive.
     */
    protected function writeTrailerBlock(): void
    {
        $this->outputStream->write(str_repeat("\0", 1024));
    }
}
