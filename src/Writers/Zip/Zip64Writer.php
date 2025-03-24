<?php

namespace PhpArchiveStream\Writers\Zip;

use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Writers\Zip\IO\OutputStream;
use PhpArchiveStream\Writers\Zip\Compressors\Compressor;
use PhpArchiveStream\Writers\Zip\Compressors\DeflateCompressor;
use PhpArchiveStream\Writers\Zip\Compressors\StoreCompressor;
use PhpArchiveStream\Writers\Zip\IO\InputStream;
use PhpArchiveStream\Writers\Zip\Records\CentralDirectoryFileHeader;
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Records\Fields\GeneralPurposeBitFlag;
use PhpArchiveStream\Writers\Zip\Records\Fields\Version;
use PhpArchiveStream\Writers\Zip\Records\LocalFileHeader;
use PhpArchiveStream\Writers\Zip\Zip64Records\DataDescriptor;
use PhpArchiveStream\Writers\Zip\Zip64Records\EndOfCentralDirectoryLocator;
use PhpArchiveStream\Writers\Zip\Zip64Records\EndOfCentralDirectoryRecord as Zip64EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Zip64Records\ExtraField;

class Zip64Writer
{
    protected ?OutputStream $outputStream;

    protected array $centralDirectoryHeaders = [];

    protected string $defaultCompressor;

    protected int $version = Version::ZIP64;
    protected static int $versionMadeBy = 0x603;

    public function __construct(string $outputPath)
    {
        $this->outputStream = OutputStream::open($outputPath);

        /**
         * @todo Should be changed dinamically
         * @todo Should be injected via a configuration class
         */
        $this->defaultCompressor = DeflateCompressor::class;
        // $this->defaultCompressor = StoreCompressor::class;
    }

    public static function create(string $outputPath): static
    {
        return new static($outputPath);
    }

    public function addFile(InputStream $stream, string $fileName): void
    {
        $compressor = new $this->defaultCompressor;

        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader()
            ->setCompressionMethod($compressor);

        $lastModificationUnixTime = time();

        $localHeaderOffset = $this->outputStream->getBytesWritten();

        $this->writeLocalFileHeader($fileName, $generalPurposeBitFlag, $lastModificationUnixTime, $compressor::bitFlag());

        list($crc32Value, $compressedSize, $uncompressedSize) = $this->writeFile($stream, $compressor);

        $this->writeDataDescriptor($crc32Value, $compressedSize, $uncompressedSize);

        $this->centralDirectoryHeaders[] = $this->generateCentralDirectoryFileHeader(
            $fileName,
            $generalPurposeBitFlag,
            $lastModificationUnixTime,
            $crc32Value,
            $compressedSize,
            $uncompressedSize,
            $localHeaderOffset,
            $compressor::bitFlag()
        );
    }

    public function finish(): void
    {
        $centralDirectoryOffset = $this->outputStream->getBytesWritten();
        $sizeOfCentralDirectory = 0;

        foreach ($this->centralDirectoryHeaders as $header) {
            $this->outputStream->write($header);

            $sizeOfCentralDirectory += strlen($header);
        }

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

        $this->outputStream->write(EndOfCentralDirectoryRecord::generate(
            diskNumber: 0,
            diskStart: 0,
            numberOfCentralDirectoryRecords: min(count($this->centralDirectoryHeaders), 0xFFFF),
            totalCentralDirectoryRecords: min(count($this->centralDirectoryHeaders), 0xFFFF),
            centralDirectorySize: $sizeOfCentralDirectory,
            centralDirectoryOffset: $centralDirectoryOffset,
        ));

        $this->outputStream->close();
    }

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

    protected function writeFile(InputStream $stream, Compressor $compressor): array
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
            $extraField = ExtraField::generate(
                $uncompressedSize,
                $compressedSize,
                $localHeaderOffset,
                0
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
            extraField: $extraField,
            fileName: $fileName
        );
    }
}
