<?php

namespace PhpArchiveStream\Concerns;

use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

trait CreatesStreams
{
    /**
     * The mode used for gzopen streams.
     *
     * @var string
     */
    public static $gzopenMode = 'wb9';

    /**
     * The mode used for fopen streams.
     *
     * @var string
     */
    public static $fopenMode = 'wb';

    /**
     * Get a stream resource for the given path.
     *
     * @param  string  $destination  The path to the destination file.
     * @return resource The stream resource.
     *
     * @throws CouldNotOpenStreamException If the stream cannot be opened.
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
