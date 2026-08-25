<?php

declare(strict_types=1);

namespace PhpArchiveStream\Writers\SevenZip;

use InvalidArgumentException;
use PhpArchiveStream\Compressors\SevenZip\Lzma2Compressor;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Contracts\IO\SeekableWriteStream;
use PhpArchiveStream\Contracts\SevenZip\SevenZipCompressor;
use PhpArchiveStream\Contracts\Writers\Writer;
use PhpArchiveStream\Hashers\CRC32;
use PhpArchiveStream\Writers\SevenZip\Records\Folder;
use PhpArchiveStream\Writers\SevenZip\Records\Header;
use PhpArchiveStream\Writers\SevenZip\Records\SignatureHeader;

/**
 * Writes 7z archives in a memory-friendly, streaming fashion.
 *
 * The 7z format is not natively streaming-friendly: the 32-byte signature
 * header lives at the very start of the file and references the total packed
 * size, the size, and the CRC of the metadata header, while the metadata
 * header itself is written at the very end. The first bytes of the archive
 * can therefore only be finalized once every file has been compressed.
 *
 * To reconcile this with streaming, this writer:
 *
 * 1. Reserves a 32-byte slot at the start of the archive;
 * 2. Streams each file's compressed data straight to the output;
 * 3. Appends the metadata header at the end;
 * 4. Seeks back to the start and patches the signature header.
 *
 * This requires a {@see SeekableWriteStream}. The writer itself is agnostic
 * to how that capability is provided: it may be a naturally seekable
 * destination (e.g. a local file) or a non-seekable destination wrapped in a
 * buffering decorator such as `PhpArchiveStream\IO\Output\SpoolWriteStream`.
 * The stream layer is responsible for supplying an appropriate stream.
 *
 * @internal
 */
class SevenZipWriter implements Writer
{
    /**
     * The seekable output stream where the archive will be written.
     */
    protected ?SeekableWriteStream $outputStream;

    /**
     * The total number of packed bytes written.
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
     * Options forwarded to the default compressor's `init()` factory.
     *
     * @var array<string, mixed>
     */
    protected array $compressorOptions = [];

    /**
     * Create a new SevenZipWriter instance.
     *
     * @param  SeekableWriteStream  $outputStream  The seekable output stream where the archive will be written.
     * @param  array<string, mixed>  $config  Configuration options for the writer. Supports `compressor` and `compressorOptions`.
     */
    public function __construct(SeekableWriteStream $outputStream, array $config = [])
    {
        $this->outputStream = $outputStream;

        // Reserve the 32-byte signature header slot. It is patched with the
        // real values once the archive is finished.
        $this->outputStream->write(str_repeat("\0", 32));

        $this->setDefaultCompressor(
            $config['compressor'] ?? Lzma2Compressor::class,
            $config['compressorOptions'] ?? []
        );
    }

    /**
     * Set the default compressor class to use for compression.
     *
     * @param  string  $compressor  The fully qualified class name of the compressor.
     * @param  array<string, mixed>  $options  Options forwarded to the compressor's `init()` factory.
     *
     * @throws InvalidArgumentException If the provided class is not a valid compressor and coder.
     */
    public function setDefaultCompressor(string $compressor, array $options = []): void
    {
        if (! is_subclass_of($compressor, SevenZipCompressor::class)) {
            throw new InvalidArgumentException('Invalid compressor class: '.$compressor);
        }

        $this->defaultCompressor = $compressor;
        $this->compressorOptions = $options;
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

        /** @var SevenZipCompressor $compressor */
        $compressor = ($this->defaultCompressor)::init($this->compressorOptions);

        $crc32 = CRC32::init();
        $unpackSize = 0;
        $packedSize = 0;

        foreach ($stream->read() as $chunk) {
            $crc32->update($chunk);
            $unpackSize += strlen($chunk);

            $compressed = $compressor->compress($chunk);
            $packedSize += strlen($compressed);

            $this->outputStream->write($compressed);
        }

        $final = $compressor->finish();
        $packedSize += strlen($final);

        $this->outputStream->write($final);

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

        $signature = SignatureHeader::generate(
            nextHeaderOffset: $this->totalPackedSize,
            nextHeaderSize: strlen($header),
            nextHeaderCrc: crc32($header),
        );

        // The metadata header is appended last, then the signature header slot
        // reserved at the start is patched with the real values.
        $this->outputStream->write($header);
        $this->outputStream->seek(0);
        $this->outputStream->write($signature);

        $this->outputStream->close();
        $this->outputStream = null;
    }
}
