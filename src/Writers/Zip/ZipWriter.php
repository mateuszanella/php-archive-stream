<?php

namespace PhpArchiveStream\Writers\Zip;

use Error;
use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Writers\Zip\IO\OutputStream;
use PhpArchiveStream\Writers\Zip\Compressors\Compressor;
use PhpArchiveStream\Writers\Zip\Compressors\StoreCompressor;
use PhpArchiveStream\Writers\Zip\IO\InputStream;
use PhpArchiveStream\Writers\Zip\Records\CentralDirectoryFileHeader;
use PhpArchiveStream\Writers\Zip\Records\DataDescriptor;
use PhpArchiveStream\Writers\Zip\Records\EndOfCentralDirectoryRecord;
use PhpArchiveStream\Writers\Zip\Records\Fields\GeneralPurposeBitFlag;
use PhpArchiveStream\Writers\Zip\Records\Fields\Version;
use PhpArchiveStream\Writers\Zip\Records\LocalFileHeader;

class ZipWriter
{
    protected ?OutputStream $outputStream;

    protected array $centralDirectoryHeaders = [];

    protected Compressor $compressor;

    protected int $version = Version::BASE;
    protected static int $versionMadeBy = 0x603;

    public function __construct(string $outputPath)
    {
        $this->outputStream = OutputStream::open($outputPath);

        /**
         * @todo Should be changed dinamically
         * @todo Should be injected via a configuration class
         */
        $this->compressor = new StoreCompressor;
    }

    public static function create(string $outputPath): static
    {
        return new static($outputPath);
    }

    public function addFile(InputStream $stream, string $fileName): void
    {
        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader(true)
            ->setCompressionMethod($this->compressor);

        $lastModificationUnixTime = time();

        $localHeaderOffset = $this->outputStream->getBytesWritten();

        $this->writeLocalFileHeader($fileName, $generalPurposeBitFlag, $lastModificationUnixTime);

        list($crc32Value, $compressedSize, $uncompressedSize) = $this->writeFile($stream);

        error_log("CRC32: $crc32Value, Compressed Size: $compressedSize, Uncompressed Size: $uncompressedSize");

        $this->writeDataDescriptor($crc32Value, $compressedSize, $uncompressedSize);

        $this->centralDirectoryHeaders[] = $this->generateCentralDirectoryFileHeader(
            $fileName,
            $generalPurposeBitFlag,
            $lastModificationUnixTime,
            $crc32Value,
            $compressedSize,
            $uncompressedSize,
            $localHeaderOffset
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
    }

    protected function writeLocalFileHeader(string $fileName, GeneralPurposeBitFlag $generalPurposeBitFlag, int $lastModificationUnixTime): void
    {
        $this->outputStream->write(LocalFileHeader::generate(
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $this->compressor::bitFlag(),
            lastModificationUnixTime: $lastModificationUnixTime,
            crc32: 0,
            compressedSize: 0,
            uncompressedSize: 0,
            fileName: $fileName
        ));
    }

    protected function writeFile(InputStream $stream): array
    {
        $crc32 = CRC32::init();
        $compressedSize = 0;
        $uncompressedSize = 0;

        foreach ($stream->read() as $chunk) {
            $crc32->update($chunk);
            $uncompressedSize += strlen($chunk);

            $compressedChunk = $this->compressor->compress($chunk);
            $compressedSize += strlen($compressedChunk);

            $this->outputStream->write($compressedChunk);
        }

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
    ): string {
        return CentralDirectoryFileHeader::generate(
            versionMadeBy: static::$versionMadeBy,
            minimumVersion: $this->version,
            generalPurposeBitFlag: $generalPurposeBitFlag->getValue(),
            compressionMethod: $this->compressor::bitFlag(),
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
