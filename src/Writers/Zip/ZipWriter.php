<?php

namespace PhpArchiveStream\Writers\Zip;

use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Compressors\Compressor;
use PhpArchiveStream\Compressors\DeflateCompressor;
use PhpArchiveStream\Compressors\StoreCompressor;
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
    protected ?WriteStream $outputStream;

    protected array $centralDirectoryHeaders = [];

    protected string $defaultCompressor;

    protected int $version = Version::BASE;

    protected static int $versionMadeBy = 0x603;

    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;

        $this->setDefaultCompressor(DeflateCompressor::class);
    }

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

    public function addFile(ReadStream $stream, string $fileName): void
    {
        $compressor = new $this->defaultCompressor;

        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader(true)
            ->setCompressionMethod($compressor);

        $lastModificationUnixTime = time();

        $localHeaderOffset = $this->outputStream->getBytesWritten();

        $this->writeLocalFileHeader($fileName, $generalPurposeBitFlag, $lastModificationUnixTime, $compressor::zipBitFlag());

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
            $compressor::zipBitFlag()
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
