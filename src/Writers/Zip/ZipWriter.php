<?php

namespace PhpArchiveStream\Writers\Zip;

use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Writers\Zip\IO\OutputStream;
use PhpArchiveStream\Writers\Zip\Compressors\Compressor;
use PhpArchiveStream\Writers\Zip\Compressors\StoreCompressor;
use PhpArchiveStream\Writers\Zip\IO\InputStream;
use PhpArchiveStream\Writers\Zip\Records\DataDescriptor;
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

    public function create(string $outputPath): self
    {
        return new self($outputPath);
    }

    public function addFile(InputStream $stream, string $fileName): void
    {
        $generalPurposeBitFlag = GeneralPurposeBitFlag::create()
            ->setZeroHeader(true)
            ->setCompressionMethod($this->compressor);

        $lastModificationUnixTime = time();

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

        $this->outputStream->write($localFileHeader);

        $crc32 = CRC32::init();
        $compressedSize = 0;
        $uncompressedSize = 0;

        foreach($stream->read() as $chunk) {
            $crc32->update($chunk);
            $uncompressedSize += strlen($chunk);

            $compressedChunk = $this->compressor->compress($chunk);
            $compressedSize += strlen($compressedChunk);

            $this->outputStream->write($compressedChunk);
        }

        $crc32Value = $crc32->finish();

        $dataDescriptor = DataDescriptor::generate(
            $crc32Value,
            $compressedSize,
            $uncompressedSize
        );

        $this->outputStream->write($dataDescriptor);
    }

    public function finish(): void
    {
        // Add central directory

        // Add end of central directory
    }
}
