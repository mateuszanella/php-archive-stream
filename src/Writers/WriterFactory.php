<?php

namespace LaravelFileStream\Writers;

use Illuminate\Contracts\Foundation\Application;
use InvalidArgumentException;
use LaravelFileStream\Writers\Tar\Tar;
use LaravelFileStream\Writers\Zip\Zip;

class WriterFactory
{
    public static array $registeredWriters = [
        'zip' => Zip::class,
        'tar' => Tar::class
    ];

    public static function to(string $path, Application $app)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (! array_key_exists($extension, self::$registeredWriters)) {
            throw new InvalidArgumentException("Writer for extension {$extension} not found");
        }

        return $app->make(self::$registeredWriters[$extension]);
    }
}
