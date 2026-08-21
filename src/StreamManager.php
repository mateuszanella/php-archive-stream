<?php

namespace PhpArchiveStream;

use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;
use PhpArchiveStream\IO\Output\GzOutputStream;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\SpoolWriteStream;

/**
 * Registers and resolves the output streams used by archive formats.
 *
 * `StreamManager` is the stream counterpart to {@see ArchiveManager}: it maps
 * an archive extension to a builder that opens a destination and wraps it in
 * the appropriate {@see WriteStream}. Opening and wrapping are kept together
 * so that the low-level open function (`fopen`, `gzopen`, ...) always matches
 * the stream implementation, which scales cleanly to new compressed streams
 * (gzip, bzip2, xz, ...).
 *
 * Archives and streams are intentionally orthogonal — any archive format can
 * consume any stream, and streams can be registered, overridden, or extended
 * independently of the archive formats. Stream builders receive the raw
 * destination plus any stream-specific configuration.
 */
class StreamManager
{
    /**
     * The registered stream builders, keyed by extension.
     *
     * @var array<string, callable(string, array<string, mixed>): WriteStream>
     */
    protected array $builders = [];

    /**
     * Create a new StreamManager instance with the default stream builders.
     */
    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Register a stream builder for the given extension.
     *
     * @param  callable(string, array<string, mixed>): WriteStream  $builder
     */
    public function register(string $extension, callable $builder): void
    {
        $this->builders[$extension] = $builder;
    }

    /**
     * Open the given destination and wrap it in the stream registered for the extension.
     *
     * @param  array<string, mixed>  $config  Configuration options for the stream.
     *
     * @throws InvalidArgumentException If no builder is registered for the extension.
     */
    public function make(string $extension, string $destination, array $config = []): WriteStream
    {
        if (! isset($this->builders[$extension])) {
            throw new InvalidArgumentException("Unsupported destination type: {$extension}");
        }

        return ($this->builders[$extension])($destination, $config);
    }

    /**
     * Register the default stream builders.
     */
    protected function registerDefaults(): void
    {
        $this->register('zip', function (string $destination) {
            return new OutputStream($this->openWith($destination, 'wb', 'fopen'));
        });

        $this->register('tar', function (string $destination) {
            return new OutputStream($this->openWith($destination, 'wb', 'fopen'));
        });

        $this->register('tar.gz', function (string $destination) {
            return new GzOutputStream($this->openWith($destination, 'wb9', 'gzopen'));
        });

        $this->register('7z', function (string $destination, array $config = []) {
            return $this->makeSevenZip($destination, $config);
        });
    }

    /**
     * Open the destination for a 7z archive and wrap it in the appropriate stream.
     *
     * The 7z writer requires a seekable output stream (see `SevenZipWriter`).
     * Non-seekable destinations are therefore wrapped in a `SpoolWriteStream`,
     * which buffers the archive and provides the required seeking capability.
     *
     * The `streaming` configuration option controls this behavior:
     * - `auto` (default): wrap non-seekable destinations, leave seekable ones untouched;
     * - `seek`: require a seekable destination, throwing otherwise;
     * - `spool`: always wrap the destination, regardless of seekability.
     *
     * @param  array<string, mixed>  $config  Configuration options for the stream.
     *
     * @throws InvalidArgumentException If the `seek` strategy is forced on a non-seekable stream.
     */
    protected function makeSevenZip(string $destination, array $config): WriteStream
    {
        $stream = $this->openWith($destination, 'wb', 'fopen');

        $strategy = $config['streaming'] ?? 'auto';
        $seekable = (bool) (stream_get_meta_data($stream)['seekable'] ?? false);

        if ($strategy === 'seek' && ! $seekable) {
            throw new InvalidArgumentException('Cannot stream 7z archive: the destination stream is not seekable.');
        }

        if ($strategy === 'spool' || ! $seekable) {
            return new SpoolWriteStream(new OutputStream($stream));
        }

        return new OutputStream($stream);
    }

    /**
     * Open a destination using the given PHP open function.
     *
     * @param  callable(string, string): resource|false  $opener
     * @return resource The opened stream resource.
     *
     * @throws CouldNotOpenStreamException If the stream cannot be opened.
     */
    protected function openWith(string $destination, string $mode, callable $opener)
    {
        $stream = $opener($destination, $mode);

        if ($stream === false) {
            throw new CouldNotOpenStreamException($destination);
        }

        return $stream;
    }
}
