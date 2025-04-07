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

        if (!isset($this->drivers[$extension])) {
            throw new Exception("Unsupported archive type for extension: {$extension}");
        }

        return call_user_func($this->drivers[$extension], $filename);
    }

    public function config(): Config
    {
        return $this->config;
    }

    protected function registerDefaults(): void
    {
        $this->drivers['.zip'] = function ($path) {
            $useZip64 = $this->config->get('zip.enableZip64', true);

            $defaultOutputClass = $this->config->get('zip.output.default', OutputStream::class);
            if (! class_exists($defaultOutputClass)) {
                throw new Exception("Default output stream class {$defaultOutputClass} does not exist.");
            }

            $stream = $this->getStream($path);

            $outputStream = new $defaultOutputClass($stream);

            return new Zip(
                $useZip64
                    ? new Zip64Writer($outputStream)
                    : new ZipWriter($outputStream)
            );
        };

        $this->drivers['.tar'] = function ($path) {
            $defaultOutputClass = $this->config->get('tar.output.default', TarOutputStream::class);
            if (! class_exists($defaultOutputClass)) {
                throw new Exception("Default output stream class {$defaultOutputClass} does not exist.");
            }

            $stream = $this->getStream($path);

            $outputStream = new $defaultOutputClass($stream);

            return new Tar(
                new TarWriter($outputStream),
            );
        };

        $this->drivers['.tar.gz'] = function ($path) {
            $defaultOutputClass = $this->config->get('targz.output.default', TarGzOutputStream::class);
            if (! class_exists($defaultOutputClass)) {
                throw new Exception("Default output stream class {$defaultOutputClass} does not exist.");
            }

            $stream = $this->getStream($path);

            $outputStream = new $defaultOutputClass($stream);

            return new Tar(
                new TarWriter($outputStream),
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

    protected function getStream(string $path)
    {
        $stream = str_ends_with($path, '.gz')
            ? gzopen($path, 'wb9')
            : fopen($path, 'wb');

        if ($stream === false) {
            throw new Exception("Could not open stream for path: {$path}");
        }

        return $stream;
    }
}
