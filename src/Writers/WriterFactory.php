<?php

namespace PhpArchiveStream\Writers;

use InvalidArgumentException;
use PhpArchiveStream\Writers\Tar\Tar;
use PhpArchiveStream\Writers\Zip\Zip;

class WriterFactory
{
    public static array $registeredWriters = [
        'zip' => Zip::class,
        'tar' => Tar::class
    ];

    public static function to(string $path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (! array_key_exists($extension, self::$registeredWriters)) {
            throw new InvalidArgumentException("Writer for extension {$extension} not found");
        }

        return new self::$registeredWriters[$extension]($path);
    }
}
