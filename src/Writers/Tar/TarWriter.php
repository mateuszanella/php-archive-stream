<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\Tar;

use BadMethodCallException;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\Writers\Writer;
use RuntimeException;

/**
 * @internal
 */
class TarWriter implements Writer
{
    /**
     * The output stream where the TAR archive will be written.
     */
    protected ?WriteStream $outputStream;

    /**
     * Create a new TarWriter instance.
     */
    public function __construct(WriteStream $outputStream)
    {
        $this->outputStream = $outputStream;
    }

    /**
     * TAR has no per-entry compression method, so this writer cannot compress.
     *
     * @throws BadMethodCallException Always, as TAR archives do not support compression.
     */
    public function setDefaultCompressor(string $compressor, array $options = []): void
    {
        throw new BadMethodCallException('TAR archives do not support compression.');
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

        $this->stream()->close();
        $this->outputStream = null;
    }

    /**
     * Get the output stream, throwing if the archive has already been finished.
     *
     * @throws RuntimeException If {@see finish()} has already been called.
     */
    protected function stream(): WriteStream
    {
        if ($this->outputStream === null) {
            throw new RuntimeException('The archive is already finished and can no longer be written to.');
        }

        return $this->outputStream;
    }

    /**
     * Write the file data block to the TAR archive.
     */
    protected function writeFileDataBlock(ReadStream $inputStream): void
    {
        $bytesWritten = 0;

        foreach ($inputStream->read() as $chunk) {
            $bytesWritten += $this->stream()->write($chunk);
        }

        if ($bytesWritten % 512 !== 0) {
            $paddingSize = 512 - ($bytesWritten % 512);

            $this->stream()->write(str_repeat("\0", $paddingSize));
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

        $this->stream()->write($header);
    }

    /**
     * Write the trailer block to the TAR archive.
     */
    protected function writeTrailerBlock(): void
    {
        $this->stream()->write(str_repeat("\0", 1024));
    }
}
