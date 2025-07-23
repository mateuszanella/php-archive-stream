<?php

namespace PhpArchiveStream\Writers\Zip;

use InvalidArgumentException;
use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Compressors\DeflateCompressor;
use PhpArchiveStream\Contracts\Compressor;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\Writers\Zip\Records\CentralDirectoryFileHeader;
use PhpArchiveStream\Writers\Zip\Records\DataDescriptor;
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Records\Fields\GeneralPurposeBitFlag;
use PhpArchiveStream\Writers\Zip\Records\Fields\Version;
use PhpArchiveStream\Writers\Zip\Records\LocalFileHeader;

class ZipWriter implements Writer
{
    /**
     * The output stream where the ZIP archive will be written.
     */
    protected ?WriteStream $outputStream;

    /**
     * The headers for the central directory.
     */
    protected array $centralDirectoryHeaders = [];

    /**
     * The default compressor class to use for compression.
     */
    protected string $defaultCompressor;

    /**
     * The version of the ZIP format being used.
     */
    protected int $version = Version::BASE;

    /**
     * The version made by field for the ZIP format.
     */
    protected static int $versionMadeBy = 0x603;

    /**
     * Create a new ZipWriter instance, that supports zip version 1.0 and 2.0.
     *
     * @param WriteStream $outputStream The output stream where the ZIP archive will be written.
     * @param array $config Configuration options for the writer, unused in this implementation.
     */
    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;

        $this->setDefaultCompressor(DeflateCompressor::class);
    }

    /**
     * Set the default compressor class to use for compression.
     *
     * @param string $compressor The fully qualified class name of the compressor.
     * @throws InvalidArgumentException If the provided class is not a valid compressor.
     */
    public function setDefaultCompressor(string $compressor): void
    {
        if (! is_subclass_of($compressor, Compressor::class)) {
            throw new InvalidArgumentException('Invalid compressor class: ' . $compressor);
        }

        $this->defaultCompressor = $compressor;

        if ($this->defaultCompressor === DeflateCompressor::class) {
            $this->version = Version::DEFLATE;
        }
    }

    /**
     * Add a file to the ZIP archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void
    {
        $compressor = new $this->defaultCompressor;

        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader(true)
            ->setCompressionMethod($compressor);

        $lastModificationUnixTime = time();

        $localHeaderOffset = $this->outputStream->getBytesWritten();

        $this->writeLocalFileHeader($fileName, $generalPurposeBitFlag, $lastModificationUnixTime, $compressor::zipBitFlag());

        [$crc32Value, $compressedSize, $uncompressedSize] = $this->writeFile($stream, $compressor);

        $this->writeDataDescriptor($crc32Value, $compressedSize, $uncompressedSize);

        $this->centralDirectoryHeaders[] = $this->generateCentralDirectoryFileHeader(
            $fileName,
            $generalPurposeBitFlag,
            $lastModificationUnixTime,
            $crc32Value,
            $compressedSize,
            $uncompressedSize,
            $localHeaderOffset,
            $compressor::zipBitFlag()
        );
    }

    /**
     * Finish writing the ZIP archive.
     */
    public function finish(): void
    {
        $centralDirectoryOffset = $this->outputStream->getBytesWritten();
        $sizeOfCentralDirectory = 0;

        foreach ($this->centralDirectoryHeaders as $header) {
            $this->outputStream->write($header);

            $sizeOfCentralDirectory += strlen($header);
        }

        $endOfCentralDirectory = EndOfCentralDirectoryRecord::generate(
            diskNumber: 0,
            diskStart: 0,
            numberOfCentralDirectoryRecords: count($this->centralDirectoryHeaders),
            totalCentralDirectoryRecords: count($this->centralDirectoryHeaders),
            centralDirectorySize: $sizeOfCentralDirectory,
            centralDirectoryOffset: $centralDirectoryOffset,
        );

        $this->outputStream->write($endOfCentralDirectory);
        $this->outputStream->close();
        $this->outputStream = null;
    }

    /**
     * Write the local file header for a file in the ZIP archive.
     */
    protected function writeLocalFileHeader(
        string $fileName,
        GeneralPurposeBitFlag $generalPurposeBitFlag,
        int $lastModificationUnixTime,
        int $compressionMethod
    ): void {
        $this->outputStream->write(LocalFileHeader::generate(
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $compressionMethod,
            lastModificationUnixTime: $lastModificationUnixTime,
            crc32: 0,
            compressedSize: 0,
            uncompressedSize: 0,
            fileName: $fileName
        ));
    }

    /**
     * Write the file data to the ZIP archive and return the CRC32, compressed size, and uncompressed size.
     *
     * @return array<int, int, int> An array containing the CRC32 value, compressed size, and uncompressed size.
     */
    protected function writeFile(ReadStream $stream, Compressor $compressor): array
    {
        $crc32 = CRC32::init();
        $compressedSize = 0;
        $uncompressedSize = 0;

        foreach ($stream->read() as $chunk) {
            $crc32->update($chunk);
            $uncompressedSize += strlen($chunk);

            $compressedChunk = $compressor->compress($chunk);
            $compressedSize += strlen($compressedChunk);

            $this->outputStream->write($compressedChunk);
        }

        $finalCompressedChunk = $compressor->finish();
        $compressedSize += strlen($finalCompressedChunk);

        $this->outputStream->write($finalCompressedChunk);

        $crc32Value = $crc32->finish();

        return [$crc32Value, $compressedSize, $uncompressedSize];
    }

    /**
     * Write the data descriptor for the file in the ZIP archive.
     */
    protected function writeDataDescriptor(
        int $crc32Value,
        int $compressedSize,
        int $uncompressedSize
    ): void {
        $dataDescriptor = DataDescriptor::generate(
            $crc32Value,
            $compressedSize,
            $uncompressedSize
        );

        $this->outputStream->write($dataDescriptor);
    }

    /**
     * Generate the central directory file header for a file in the ZIP archive.
     */
    protected function generateCentralDirectoryFileHeader(
        string $fileName,
        GeneralPurposeBitFlag $generalPurposeBitFlag,
        int $lastModificationUnixTime,
        int $crc32Value,
        int $compressedSize,
        int $uncompressedSize,
        int $localHeaderOffset,
        int $compressionMethod
    ): string {
        return CentralDirectoryFileHeader::generate(
            versionMadeBy: static::$versionMadeBy,
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $compressionMethod,
            lastModificationUnixTime: $lastModificationUnixTime,
            crc32: $crc32Value,
            compressedSize: $compressedSize,
            uncompressedSize: $uncompressedSize,
            diskNumberStart: 0,
            internalFileAttributes: 0,
            externalFileAttributes: 32,
            relativeOffsetOfLocalHeader: $localHeaderOffset,
            fileName: $fileName
        );
    }
}
