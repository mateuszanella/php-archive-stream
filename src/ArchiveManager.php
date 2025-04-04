<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\TarGzOutputStream;
use PhpArchiveStream\IO\Output\TarOutputStream;
use PhpArchiveStream\Writers\Tar\TarWriter;
use PhpArchiveStream\Writers\Zip\Zip64Writer;
use PhpArchiveStream\Writers\Zip\ZipWriter;

class ArchiveManager
{
    protected $drivers = [];

    /**
     * @todo should be an instance of the config class
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->registerDefaults();
    }

    public function register(string $extension, callable $factory): void
    {
        $this->drivers[$extension] = $factory;
    }

    public function create(string $filename, array $options = []): Archive
    {
        $extension = $this->extractExtension($filename);

        if (!isset($this->drivers[$extension])) {
            throw new Exception("Unsupported archive type for extension: {$extension}");
        }

        $mergedOptions = array_merge($this->config, $options);

        return call_user_func($this->drivers[$extension], $filename, $mergedOptions);
    }

    protected function registerDefaults(): void
    {
        $this->drivers['.zip'] = function ($path, array $options) {
            $useZip64 = $options['zip64'] ?? true;
            $outputStream = OutputStream::open($path);

            $writer = $useZip64
                ? new Zip64Writer($outputStream)
                : new ZipWriter($outputStream);

            return new Zip($writer, $options);
        };

        $this->drivers['.tar'] = function ($path, array $options) {
            $outputStream = TarOutputStream::open($path);

            return new Tar(
                new TarWriter($outputStream),
                $options
            );
        };

        $this->drivers['.tar.gz'] = function ($path, array $options) {
            $outputStream = TarGzOutputStream::open($path);

            return new Tar(
                new TarWriter($outputStream),
                $options
            );
        };
    }

    protected function extractExtension(string $filename): string
    {
        if (preg_match('/\.tar\.gz$/i', $filename)) {
            return '.tar.gz';
        }

        return strtolower(strrchr($filename, '.'));
    }
}
