<?php

namespace PhpArchiveStream\Concerns;

use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

trait CreatesStreams
{
    public static $gzopenMode = 'wb9';

    public static $fopenMode = 'wb';

    /**
     * Get a stream resource for the given path.
     *
     * @return resource
     */
    protected static function createStream(string $destination)
    {
        $stream = str_ends_with($destination, '.gz')
            ? gzopen($destination, static::$gzopenMode)
            : fopen($destination, static::$fopenMode);

        if ($stream === false) {
            throw new CouldNotOpenStreamException($destination);
        }

        return $stream;
    }
}
