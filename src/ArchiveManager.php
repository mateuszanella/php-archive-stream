<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\TarGzOutputStream;
use PhpArchiveStream\IO\Output\TarOutputStream;
use PhpArchiveStream\Support\WriteStreamFactory;
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
    }

    /**
     * Register a new driver with the manager.
     *
     * @param  string  $extension
     * @param  callable(string, \PhpArchiveStream\Config): Archive  $factory
     */
    public function register(string $extension, callable $factory): void
    {
        $this->drivers[$extension] = $factory;
    }

    /**
     * Create a new archive instance.
     *
     * @param  string  $filename
     */
    public function create(string $filename): Archive
    {
        $extension = $this->extractExtension($filename);

        if (! isset($this->drivers[$extension])) {
            throw new Exception("Unsupported archive type for extension: {$extension}");
        }

        return ($this->drivers[$extension])($filename, $this->config);
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
        $this->register('.zip', function ($path, $config) {
            $useZip64 = $config->get('zip.enableZip64', true);
            $defaultChunkSize = $config->get('zip.input.chunkSize', 4096);
            $outputStream = WriteStreamFactory::create(
                $config->get('zip.output.default', OutputStream::class),
                $path,
            );

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream)
                    : new ZipWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar', function ($path, $config) {
            $defaultChunkSize = $config->get('zip.input.chunkSize', 512);
            $outputStream = WriteStreamFactory::create(
                $config->get('tar.output.default', TarOutputStream::class),
                $path,
            );

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar.gz', function ($path, $config) {
            $defaultChunkSize = $config->get('zip.input.chunkSize', 512);
            $outputStream = WriteStreamFactory::create(
                $config->get('targz.output.default', TarGzOutputStream::class),
                $path,
            );

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });
    }

    /**
     * Extract the file extension from the filename.
     */
    protected function extractExtension(string $filename): string
    {
        if (preg_match('/\.tar\.gz$/i', $filename)) {
            return '.tar.gz';
        }

        return strtolower(strrchr($filename, '.'));
    }
}
