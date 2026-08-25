<?php

declare(strict_types=1);

namespace PhpArchiveStream;

use PhpArchiveStream\Archives\SevenZip;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Exceptions\UnsupportedArchiveTypeException;
use PhpArchiveStream\Writers\SevenZip\SevenZipWriter;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\Zip\Zip64Writer;
use PhpArchiveStream\Writers\Zip\ZipWriter;

/**
 * The primary entry point for creating archives.
 *
 * @api
 *
 * @phpstan-consistent-constructor
 */
class ArchiveManager
{
    /**
     * The array of registered driver constructor callbacks.
     *
     * @var array<string, callable(string|array<string>, \PhpArchiveStream\ConfigManager): Archive>
     */
    protected array $drivers = [];

    /**
     * The array of registered driver aliases.
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * The configuration instance.
     */
    protected ConfigManager $config;

    /**
     * The destination parser instance.
     */
    protected DestinationManager $destination;

    /**
     * Create a new ArchiveManager instance.
     *
     * @param  ConfigManager  $config  The configuration manager instance.
     * @param  DestinationManager  $destination  The destination manager instance.
     */
    public function __construct(ConfigManager $config, DestinationManager $destination)
    {
        $this->config = $config;
        $this->destination = $destination;

        $this->registerDefaults();
        $this->registerAliases();
    }

    /**
     * Convenience entry point to create a new ArchiveManager instance with default values.
     *
     * @param  array<string, mixed>  $config
     */
    public static function make(array $config = []): static
    {
        return new static(
            new ConfigManager($config),
            new DestinationManager(new StreamManager),
        );
    }

    /**
     * Register a new driver.
     *
     * @param  callable(string|array<string>, \PhpArchiveStream\ConfigManager): Archive  $factory
     */
    public function register(string $extension, callable $factory): void
    {
        $this->drivers[$extension] = $factory;
    }

    /**
     * Register a new driver alias.
     */
    public function alias(string $alias, string $extension): void
    {
        if (! isset($this->drivers[$extension])) {
            throw new UnsupportedArchiveTypeException($extension);
        }

        $this->aliases[$alias] = $extension;
    }

    /**
     * Create a new archive instance.
     *
     * @param  string|array<string>  $destination
     */
    public function create(string|array $destination, ?string $extension = null): Archive
    {
        $extension ??= $this->destination->extractCommonExtension($destination);
        $extension = strtolower($extension);

        if (isset($this->aliases[$extension])) {
            $extension = $this->aliases[$extension];
        }

        if (! isset($this->drivers[$extension])) {
            throw new UnsupportedArchiveTypeException($extension);
        }

        return ($this->drivers[$extension])($destination, $this->config);
    }

    /**
     * Get the configuration instance.
     */
    public function config(): ConfigManager
    {
        return $this->config;
    }

    /**
     * Get the destination manager instance.
     */
    public function destination(): DestinationManager
    {
        return $this->destination;
    }

    /**
     * Get the stream manager instance.
     */
    public function stream(): StreamManager
    {
        return $this->destination->stream();
    }

    /**
     * Replace the default `Content-Disposition` filename with the real
     * destination basename, if one can be determined.
     *
     * @param  string|array<string>  $destination
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function headersFor(string|array $destination, array $headers): array
    {
        $fileName = $this->fileNameFor($destination);

        if ($fileName === null || ! isset($headers['Content-Disposition'])) {
            return $headers;
        }

        $filename = 'filename="'.$fileName.'"';

        $replaced = preg_replace(
            '/filename="[^"]*"/',
            $filename,
            $headers['Content-Disposition'],
            1
        );

        if ($replaced !== null) {
            $headers['Content-Disposition'] = $replaced;
        }

        return $headers;
    }

    /**
     * Resolve a downloadable filename for the given destination.
     *
     * Non-file destinations such as `php://output` have no usable name and
     * yield `null`, keeping the configured default.
     *
     * @param  string|array<string>  $destination
     */
    protected function fileNameFor(string|array $destination): ?string
    {
        foreach ((array) $destination as $dest) {
            if (! str_starts_with($dest, 'php://')) {
                return basename($dest);
            }
        }

        return null;
    }

    /**
     * Register the default drivers.
     */
    protected function registerDefaults(): void
    {
        $this->register('zip', function (string|array $destination, ConfigManager $config) {
            $useZip64 = $config->get('zip.enableZip64', true);
            $defaultChunkSize = $config->get('zip.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('zip.headers'));

            $outputStream = $this->destination->getStream($destination, 'zip', $headers);

            $writerConfig = [
                'compressor'        => $config->get('zip.compressor'),
                'compressorOptions' => $config->get('zip.compressorOptions', []),
            ];

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream, $writerConfig)
                    : new ZipWriter($outputStream, $writerConfig),
                $defaultChunkSize
            );
        });

        $this->register('tar', function (string|array $destination, ConfigManager $config) {
            $defaultChunkSize = $config->get('tar.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('tar.headers'));

            $outputStream = $this->destination->getStream($destination, 'tar', $headers);

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('tar.gz', function (string|array $destination, ConfigManager $config) {
            $defaultChunkSize = $config->get('targz.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('targz.headers'));

            $outputStream = $this->destination->getStream($destination, 'tar.gz', $headers);

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('tar.bz2', function (string|array $destination, ConfigManager $config) {
            $defaultChunkSize = $config->get('tarbz2.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('tarbz2.headers'));

            $outputStream = $this->destination->getStream($destination, 'tar.bz2', $headers);

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('tar.xz', function (string|array $destination, ConfigManager $config) {
            $defaultChunkSize = $config->get('tarxz.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('tarxz.headers'));

            $outputStream = $this->destination->getStream($destination, 'tar.xz', $headers);

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('7z', function (string|array $destination, ConfigManager $config) {
            $defaultChunkSize = $config->get('7z.input.chunkSize', 1048576);

            $headers = $this->headersFor($destination, $config->get('7z.headers'));

            $outputStream = $this->destination->getStream($destination, '7z', $headers, [
                'streaming' => $config->get('7z.streaming', 'auto'),
            ]);

            return new SevenZip(
                new SevenZipWriter($outputStream, [
                    'compressor'        => $config->get('7z.compressor'),
                    'compressorOptions' => $config->get('7z.compressorOptions', []),
                ]),
                $defaultChunkSize
            );
        });
    }

    /**
     * Register the default driver aliases.
     */
    protected function registerAliases(): void
    {
        $this->alias('tgz', 'tar.gz');
        $this->alias('tbz2', 'tar.bz2');
        $this->alias('txz', 'tar.xz');
    }
}
