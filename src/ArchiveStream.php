<?php

namespace PhpArchiveStream;

use Exception;
use PhpArchiveStream\Archives\Tar;
use PhpArchiveStream\Archives\Zip;
use PhpArchiveStream\Contracts\Archive;

class ArchiveStream
{
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
