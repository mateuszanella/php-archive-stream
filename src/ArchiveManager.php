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
    protected array $drivers = [];

    protected Config $config;

    public function __construct(array $config = [])
    {
        $this->config = new Config($config);

        $this->registerDefaults();
    }

    public function register(string $extension, callable $factory): void
    {
        $this->drivers[$extension] = $factory;
    }

    public function create(string $filename): Archive
    {
        $extension = $this->extractExtension($filename);

        if (! isset($this->drivers[$extension])) {
            throw new Exception("Unsupported archive type for extension: {$extension}");
        }

        return ($this->drivers[$extension])($filename);
    }

    public function config(): Config
    {
        return $this->config;
    }

    protected function registerDefaults(): void
    {
        $this->register('.zip', function ($path) {
            $useZip64 = $this->config->get('zip.enableZip64', true);
            $defaultChunkSize = $this->config->get('zip.input.chunkSize', 4096);
            $outputStream = WriteStreamFactory::create(
                $this->config->get('zip.output.default', OutputStream::class),
                $path,
            );

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream)
                    : new ZipWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar', function ($path) {
            $defaultChunkSize = $this->config->get('zip.input.chunkSize', 512);
            $outputStream = WriteStreamFactory::create(
                $this->config->get('tar.output.default', TarOutputStream::class),
                $path,
            );

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });

        $this->register('.tar.gz', function ($path) {
            $defaultChunkSize = $this->config->get('zip.input.chunkSize', 512);
            $outputStream = WriteStreamFactory::create(
                $this->config->get('targz.output.default', TarGzOutputStream::class),
                $path,
            );

            return new Tar(
                new TarWriter($outputStream),
                $defaultChunkSize
            );
        });
    }

    protected function extractExtension(string $filename): string
    {
        if (preg_match('/\.tar\.gz$/i', $filename)) {
            return '.tar.gz';
        }

        return strtolower(strrchr($filename, '.'));
    }
}
