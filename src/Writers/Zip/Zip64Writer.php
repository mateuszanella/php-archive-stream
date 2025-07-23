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
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Records\Fields\GeneralPurposeBitFlag;
use PhpArchiveStream\Writers\Zip\Records\Fields\Version;
use PhpArchiveStream\Writers\Zip\Records\LocalFileHeader;
use PhpArchiveStream\Writers\Zip\Zip64Records\DataDescriptor;
use PhpArchiveStream\Writers\Zip\Zip64Records\EndOfCentralDirectoryLocator;
use PhpArchiveStream\Writers\Zip\Zip64Records\EndOfCentralDirectoryRecord as Zip64EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Zip64Records\ExtraField;

class Zip64Writer implements Writer
{
    /**
     * The output stream where the ZIP archive will be written.
     */
    protected ?WriteStream $outputStream;

    /**
     * The version of the ZIP file.
     */
    protected int $version = Version::ZIP64;

    /**
     * The version made by field for the ZIP file.
     */
    protected static int $versionMadeBy = 0x603;

    /**
     * The central directory headers collected during the writing process.
     */
    protected array $centralDirectoryHeaders = [];

    /**
     * The default compressor class to use for compression.
     */
    protected string $defaultCompressor;

    /**
     * Create a new Zip64Writer instance, that supports zip version 4.5.
     *
     * @param WriteStream $outputStream The output stream where the ZIP archive will be written.
     * @param array $config Configuration options for the writer, unused in this implementation.
     */
    public function __construct(WriteStream $outputPath, array $config = [])
    {
        $this->outputStream = $outputPath;

        $this->setDefaultCompressor(DeflateCompressor::class);
    }

    /**
     * Set the default compressor class to use for compression.
     *
     * @param string $compressor The fully qualified class name of the compressor.
     * @throws InvalidArgumentException If the compressor class is not valid.
     */
    public function setDefaultCompressor(string $compressor): void
    {
        if (! is_subclass_of($compressor, Compressor::class)) {
            throw new InvalidArgumentException('Invalid compressor class: ' . $compressor);
        }

        $this->defaultCompressor = $compressor;
    }

    /**
     * Add a file to the ZIP archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void
    {
        $compressor = new $this->defaultCompressor;

        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader()
            ->setCompressionMethod($compressor);

        $lastModificationUnixTime = time();

        $localHeaderOffset = $this->outputStream->getBytesWritten();

        $this->writeLocalFileHeader(
            $fileName,
            $generalPurposeBitFlag,
            $lastModificationUnixTime,
            $compressor::zipBitFlag(),
            $localHeaderOffset
        );

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

        if (
            count($this->centralDirectoryHeaders) >= 0xFFFF
            || $centralDirectoryOffset > 0xFFFFFFFF
            || $sizeOfCentralDirectory > 0xFFFFFFFF
        ) {
            $this->outputStream->write(Zip64EndOfCentralDirectoryRecord::generate(
                versionMadeBy: static::$versionMadeBy,
                versionNeededToExtract: $this->version,
                numberOfThisDisk: 0,
                numberOfTheDiskWithTheStartOfTheCentralDirectory: 0,
                numberOfCentralDirectoryEntriesOnThisDisk: count($this->centralDirectoryHeaders),
                numberOfCentralDirectoryEntries: count($this->centralDirectoryHeaders),
                centralDirectorySize: $sizeOfCentralDirectory,
                centralDirectoryOffsetOnDisk: $centralDirectoryOffset,
                extensibleDataSector: ''
            ));

            $this->outputStream->write(EndOfCentralDirectoryLocator::generate(
                numberOfTheDiskWithZip64CentralDirectoryStart: 0,
                zip64centralDirectoryStartOffsetOnDisk: $centralDirectoryOffset + $sizeOfCentralDirectory,
                totalNumberOfDisks: 1
            ));
        }

        $this->outputStream->write(EndOfCentralDirectoryRecord::generate(
            diskNumber: 0,
            diskStart: 0,
            numberOfCentralDirectoryRecords: min(count($this->centralDirectoryHeaders), 0xFFFF),
            totalCentralDirectoryRecords: min(count($this->centralDirectoryHeaders), 0xFFFF),
            centralDirectorySize: min($sizeOfCentralDirectory, 0xFFFFFFFF),
            centralDirectoryOffset: min($centralDirectoryOffset, 0xFFFFFFFF),
        ));

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
        int $compressionMethod,
        int $localHeaderOffset
    ): void {
        $extraField = $this->buildExtraField(
            originalSize: 0,
            compressedSize: 0,
            relativeHeaderOffset: $localHeaderOffset > 0xFFFFFFFF
                ? $localHeaderOffset
                : null,
        );

        $this->outputStream->write(LocalFileHeader::generate(
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $compressionMethod,
            lastModificationUnixTime: $lastModificationUnixTime,
            crc32: 0,
            compressedSize: 0xFFFFFFFF,
            uncompressedSize: 0xFFFFFFFF,
            fileName: $fileName,
            extraField: $extraField,
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
        $extraField = '';

        if (
            $compressedSize > 0xFFFFFFFF ||
            $uncompressedSize > 0xFFFFFFFF ||
            $localHeaderOffset > 0xFFFFFFFF
        ) {
            $extraField = $this->buildExtraField(
                originalSize: $uncompressedSize > 0xFFFFFFFF
                    ? $uncompressedSize
                    : null,
                compressedSize: $compressedSize > 0xFFFFFFFF
                    ? $compressedSize
                    : null,
                relativeHeaderOffset: $localHeaderOffset > 0xFFFFFFFF
                    ? $localHeaderOffset
                    : null,
            );
        }

        return CentralDirectoryFileHeader::generate(
            versionMadeBy: static::$versionMadeBy,
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $compressionMethod,
            lastModificationUnixTime: $lastModificationUnixTime,
            crc32: $crc32Value,
            compressedSize: min($compressedSize, 0xFFFFFFFF),
            uncompressedSize: min($uncompressedSize, 0xFFFFFFFF),
            relativeOffsetOfLocalHeader: min($localHeaderOffset, 0xFFFFFFFF),
            diskNumberStart: 0,
            internalFileAttributes: 0,
            externalFileAttributes: 32,
            fileName: $fileName,
            extraField: $extraField,
        );
    }

    /**
     * Build the extra field for the ZIP64 records.
     */
    protected function buildExtraField(
        ?int $originalSize = null,
        ?int $compressedSize = null,
        ?int $relativeHeaderOffset = null
    ): string {
        return ExtraField::generate(
            originalSize: $originalSize,
            compressedSize: $compressedSize,
            relativeHeaderOffset: $relativeHeaderOffset,
            diskStartNumber: 0,
        );
    }
}
