<?php

namespace PhpArchiveStream\Writers\Zip;

use DateTime;
use DateTimeImmutable;
use PhpArchiveStream\Contracts\ReadStream;
use PhpArchiveStream\Writers\Tar\IO\OutputStream;
use PhpArchiveStream\Writers\Zip\Compressors\Compressor;
use PhpArchiveStream\Writers\Zip\Compressors\StoreCompressor;
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

    public function addFile(ReadStream $stream, string $fileName): void
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

        // Add local header

        // Add file data

        // Add data descriptor
    }

    public function finish(): void
    {
        // Add central directory

        // Add end of central directory
    }
}
