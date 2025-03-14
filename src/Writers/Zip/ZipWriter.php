<?php

namespace PhpArchiveStream\Writers\Zip;

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

        // Add local file header
        $localFileHeader = LocalFileHeader::generate(
            $this->version,
            $generalPurposeBitFlag->getValue(),
            $this->compressor::bitFlag(),
            $lastModificationUnixTime,
            0,
            0,
            0,
            $fileName
        );

        /**
         * @todo Move the logic and the write statements to functions
         */
        $this->outputStream->write($localFileHeader);

        $crc32 = CRC32::init();
        $compressedSize = 0;
        $uncompressedSize = 0;

        // Compress and write the file
        foreach($stream->read() as $chunk) {
            $crc32->update($chunk);
            $uncompressedSize += strlen($chunk);

            $compressedChunk = $this->compressor->compress($chunk);
            $compressedSize += strlen($compressedChunk);

            $this->outputStream->write($compressedChunk);
        }

        $crc32Value = $crc32->finish();

        // Add data descriptor
        $dataDescriptor = DataDescriptor::generate(
            $crc32Value,
            $compressedSize,
            $uncompressedSize
        );

        $this->outputStream->write($dataDescriptor);

        $this->centralDirectoryHeaders[] = CentralDirectoryFileHeader::generate(
            $this->version,
            $this->version,
            $generalPurposeBitFlag->getValue(),
            $this->compressor::bitFlag(),
            $lastModificationUnixTime,
            $crc32Value,
            $compressedSize,
            $uncompressedSize,
            0,
            0,
            32,
            $this->outputStream->getBytesWritten(),
            $fileName
        );
    }

    public function finish(): void
    {
        // Add central directories
        foreach($this->centralDirectoryHeaders as $header) {
            $this->outputStream->write($header);
        }

        $sizeOfCentralDirectory = 0;
        $numberOfCentralDirectoryRecords = 0;

        foreach($this->centralDirectoryHeaders as $header) {
            $sizeOfCentralDirectory += strlen($header);
            $numberOfCentralDirectoryRecords++;
        }

        // Add end of central directory
        $endOfCentralDirectory = EndOfCentralDirectoryRecord::generate(
            0,
            0,
            $numberOfCentralDirectoryRecords,
            $numberOfCentralDirectoryRecords,
            $sizeOfCentralDirectory,
            $this->outputStream->getBytesWritten()
        );

        $this->outputStream->write($endOfCentralDirectory);
        $this->outputStream->close();
    }
}
