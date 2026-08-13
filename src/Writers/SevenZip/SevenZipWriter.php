<?php

namespace PhpArchiveStream\Writers\SevenZip;

use InvalidArgumentException;
use PhpArchiveStream\Compressors\Lzma2Compressor;
use PhpArchiveStream\Contracts\Coder;
use PhpArchiveStream\Contracts\Compressor;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Writers\SevenZip\Records\Folder;
use PhpArchiveStream\Writers\SevenZip\Records\Header;
use PhpArchiveStream\Writers\SevenZip\Records\SignatureHeader;

class SevenZipWriter implements Writer
{
    /**
     * The output stream where the archive will be written.
     */
    protected ?WriteStream $outputStream;

    /**
     * The seekable spool where packed streams are buffered until the archive is finished.
     *
     * @var resource
     */
    protected $spool;

    /**
     * The total number of packed bytes written to the spool.
     */
    protected int $totalPackedSize = 0;

    /**
     * The size of each pack stream.
     *
     * @var array<int, int>
     */
    protected array $packedSizes = [];

    /**
     * The serialized folder data for each folder.
     *
     * @var array<int, string>
     */
    protected array $folderBytes = [];

    /**
     * The unpacked size of each folder.
     *
     * @var array<int, int>
     */
    protected array $unpackSizes = [];

    /**
     * The CRC32 of each file's unpacked data.
     *
     * @var array<int, int>
     */
    protected array $crcs = [];

    /**
     * The metadata for each file.
     *
     * @var array<int, array{name: string, mtime: int, attributes: int}>
     */
    protected array $files = [];

    /**
     * Whether each file has no data stream.
     *
     * @var array<int, bool>
     */
    protected array $emptyStreams = [];

    /**
     * The default compressor class to use for compression.
     */
    protected string $defaultCompressor;

    /**
     * Create a new SevenZipWriter instance.
     *
     * @param  WriteStream  $outputStream  The output stream where the archive will be written.
     * @param  array  $config  Configuration options for the writer.
     */
    public function __construct(WriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;
        $this->spool = fopen('php://temp', 'w+b');

        $this->setDefaultCompressor($config['compressor'] ?? Lzma2Compressor::class);
    }

    /**
     * Set the default compressor class to use for compression.
     *
     * @param  string  $compressor  The fully qualified class name of the compressor.
     *
     * @throws InvalidArgumentException If the provided class is not a valid compressor and coder.
     */
    public function setDefaultCompressor(string $compressor): void
    {
        if (! is_subclass_of($compressor, Compressor::class) || ! is_subclass_of($compressor, Coder::class)) {
            throw new InvalidArgumentException('Invalid compressor class: '.$compressor);
        }

        $this->defaultCompressor = $compressor;
    }

    /**
     * Add a file to the archive.
     */
    public function addFile(ReadStream $stream, string $fileName): void
    {
        $mtime = time();

        $this->files[] = [
            'name'       => $fileName,
            'mtime'      => $mtime,
            'attributes' => 0x20,
        ];

        if ($stream->size() === 0) {
            $this->emptyStreams[] = true;

            return;
        }

        $this->emptyStreams[] = false;

        /** @var Compressor&Coder $compressor */
        $compressor = new $this->defaultCompressor;

        $crc32 = CRC32::init();
        $unpackSize = 0;
        $packedSize = 0;

        foreach ($stream->read() as $chunk) {
            $crc32->update($chunk);
            $unpackSize += strlen($chunk);

            $compressed = $compressor->compress($chunk);
            $packedSize += strlen($compressed);

            fwrite($this->spool, $compressed);
        }

        $final = $compressor->finish();
        $packedSize += strlen($final);

        fwrite($this->spool, $final);

        $this->totalPackedSize += $packedSize;
        $this->packedSizes[] = $packedSize;
        $this->unpackSizes[] = $unpackSize;
        $this->crcs[] = $crc32->finish();
        $this->folderBytes[] = Folder::generate([[
            'methodId'   => $compressor->getMethodId(),
            'properties' => $compressor->getProperties(),
        ]]);
    }

    /**
     * Finish writing the archive.
     */
    public function finish(): void
    {
        $header = Header::generate(
            packedSizes: $this->packedSizes,
            folderBytes: $this->folderBytes,
            unpackSizes: $this->unpackSizes,
            crcs: $this->crcs,
            files: $this->files,
            emptyStreams: $this->emptyStreams,
        );

        $this->outputStream->write(SignatureHeader::generate(
            nextHeaderOffset: $this->totalPackedSize,
            nextHeaderSize: strlen($header),
            nextHeaderCrc: crc32($header),
        ));

        $this->writeSpool();

        $this->outputStream->write($header);
        $this->outputStream->close();
        $this->outputStream = null;
    }

    /**
     * Stream the buffered packed data to the output.
     */
    protected function writeSpool(): void
    {
        rewind($this->spool);

        while (! feof($this->spool)) {
            $chunk = fread($this->spool, 1048576);

            if ($chunk === false) {
                break;
            }

            $this->outputStream->write($chunk);
        }

        fclose($this->spool);
    }
}
