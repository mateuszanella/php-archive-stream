<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\TarGz;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;

class ArchiveStream
{
    /**
     * @todo Add configuration to select specific configs of each file format
     * @todo Refactor some of the classes to make their execution clearer
     * @todo Add PHP docs on functions
     * @todo Add tests
     * @todo Move the archive extension logic to a factory
     */
    public static function to(string $path): Archive
    {
        return match (true) {
            str_ends_with($path, '.tar.gz') => TarGz::create($path),
            str_ends_with($path, '.tar')    => Tar::create($path),
            str_ends_with($path, '.zip')    => Zip::create($path),
            default                         => throw new Exception('Unsupported archive format'),
        };
    }
}
