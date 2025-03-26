<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;

class ArchiveStream
{
    /**
     * @todo Add configuration to select specific configs of each file format
     * @todo Refactor some of the classes to make their execution clearer
     * @todo Add PHP docs on functions
     * @todo Add tests
     * @todo Try to implement .tar.gz streaming
     */
    public static function to(string $path): Archive
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return match ($extension) {
            'tar' => Tar::create($path),
            'zip' => Zip::create($path),
            default => throw new Exception('Unsupported archive format'),
        };
    }
}
