<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Support\DestinationManager;
use PhpArchiveStream\Support\StreamFactory;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\Zip\Zip64Writer;
use PhpArchiveStream\Writers\Zip\ZipWriter;

class ArchiveManager
{
    /**
     * The array of registered driver constructor callbacks.
     *
     * @var array<string, callable(string, \PhpArchiveStream\Config): Archive>
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
    protected Config $config;

    /**
     * The destination parser instance.
     */
    protected DestinationManager $destination;

    /**
     * Create a new ArchiveManager instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);

        $streamFactoryClass = $this->config->get('streamFactory', StreamFactory::class);

        $this->destination = new DestinationManager($streamFactoryClass);

        $this->registerDefaults();
        $this->registerAliases();
    }

    /**
     * Register a new driver.
     *
     * @param  callable(string|array<string>, \PhpArchiveStream\Config): Archive  $factory
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
            throw new Exception("Unsupported archive type for extension: {$extension}");
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

        if (isset($this->aliases[$extension])) {
            $extension = $this->aliases[$extension];
        }

        if (! isset($this->drivers[$extension])) {
            throw new Exception("Unsupported archive type for extension: {$extension}");
        }

        return ($this->drivers[$extension])($destination, $this->config);
    }

    /**
     * Get the configuration instance.
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * Register the default drivers.
     */
    protected function registerDefaults(): void
    {
        $this->register('zip', function (string|array $destination, Config $config) {
            $useZip64 = $config->get('zip.enableZip64', true);
            $defaultChunkSize = $config->get('zip.input.chunkSize', 1048576);

            $headers = $config->get('zip.headers');

            $outputStream = $this->destination->getStream($destination, 'zip', $headers);

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream)
                    : new ZipWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('tar', function (string|array $destination, Config $config) {
            $defaultChunkSize = $config->get('tar.input.chunkSize', 1048576);

            $headers = $config->get('tar.headers');

            $outputStream = $this->destination->getStream($destination, 'tar', $headers);

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('tar.gz', function (string|array $destination, Config $config) {
            $defaultChunkSize = $config->get('targz.input.chunkSize', 1048576);

            $headers = $config->get('targz.headers');

            $outputStream = $this->destination->getStream($destination, 'tar.gz', $headers);

            return new Tar(
                new TarWriter($outputStream),
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
    }
}
