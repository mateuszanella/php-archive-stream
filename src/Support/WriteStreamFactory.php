<?php

namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

class WriteStreamFactory
{
    public static $gzopenMode = 'wb9';

    public static $fopenMode = 'wb';

    /**
     * Create an OutputStream instance based on the configuration or default class.
     */
    public static function create(string $class, string $path): WriteStream
    {
        if (! class_exists($class)) {
            throw new InvalidArgumentException("OutputStream class {$class} does not exist.");
        }

        $stream = static::getStream($path);

        $implementation = new $class($stream);

        if (! $implementation instanceof WriteStream) {
            throw new InvalidArgumentException("OutputStream class {$class} must implement WriteStream interface.");
        }

        return $implementation;
    }

    /**
     * Get a stream resource for the given path.
     *
     * @return resource
     */
    protected static function getStream(string $path)
    {
        $stream = str_ends_with($path, '.gz')
            ? gzopen($path, static::$gzopenMode)
            : fopen($path, static::$fopenMode);

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return $stream;
    }
}
