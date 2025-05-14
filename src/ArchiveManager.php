<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\Support\Destination;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\Zip\Zip64Writer;
use PhpArchiveStream\Writers\Zip\ZipWriter;

/**
 * @todo Add support to use ArrayOutputStream when creating a file
 */
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
     * Create a new ArchiveManager instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);

        $this->registerDefaults();
        $this->registerAliases();
    }

    /**
     * Register a new driver.
     *
     * @param  string  $extension
     * @param  callable(string|array<string>, \PhpArchiveStream\Config): Archive  $factory
     */
    public function register(string $extension, callable $factory): void
    {
        $this->drivers[$extension] = $factory;
    }

    /**
     * Register a new driver alias.
     *
     * @param  string  $alias
     * @param  string  $extension
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
     * @param  string  $filename
     */
    public function create(string|array $destination): Archive
    {
        $extension = (new Destination)->extractCommonExtension($destination);

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
        $this->register('.zip', function (string|array $destination, Config $config) {
            $useZip64 = $config->get('zip.enableZip64', true);
            $defaultChunkSize = $config->get('zip.input.chunkSize', 4096);

            $outputStream = (new Destination)->parse($destination, 'zip');

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream)
                    : new ZipWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar', function (string|array $destination, Config $config) {
            $defaultChunkSize = $config->get('zip.input.chunkSize', 512);

            $outputStream = (new Destination)->parse($destination, 'tar');

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar.gz', function (string|array $destination, Config $config) {
            $defaultChunkSize = $config->get('zip.input.chunkSize', 512);

            $outputStream = (new Destination)->parse($destination, 'tar.gz');

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });
    }

    protected function registerAliases(): void
    {
        $this->alias('.tgz', '.tar.gz');
    }
}
