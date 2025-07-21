<?php

namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Concerns\CreatesStreams;
use PhpArchiveStream\Concerns\ParsesPaths;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\IO\Output\ArrayOutputStream;

class DestinationManager
{
    use ParsesPaths,
        CreatesStreams;

    /**
     * The class used to create streams.
     */
    public static string $streamFactoryClass = StreamFactory::class;

    public static function useFactory(string $class): void
    {
        if (! is_subclass_of($class, StreamFactoryContract::class)) {
            throw new InvalidArgumentException("The class must implement " . StreamFactoryContract::class);
        }

        static::$streamFactoryClass = $class;
    }

    /**
     * Extract a common extension from an array of possible destinations.
     *
     * @param  string|array<string>  $destinations
     */
    public function extractCommonExtension(string|array $destinations): string
    {
        $perceivedExtensions = [];

        if (is_string($destinations)) {
            $destinations = [$destinations];
        }

        foreach ($destinations as $destination) {
            $extension = $this->extractExtension($destination);

            if ($extension !== null) {
                $perceivedExtensions[] = $extension;
            }
        }

        if (empty($perceivedExtensions)) {
            throw new InvalidArgumentException("Could not determine the extension for destinations: " . implode(', ', $destinations));
        }

        $uniqueExtensions = array_unique($perceivedExtensions);

        if (count($uniqueExtensions) > 1) {
            throw new InvalidArgumentException("Multiple different extensions found: " . implode(', ', $destinations));
        }

        return reset($uniqueExtensions);
    }

    /**
     * Create a stream for the given destination and extension.
     */
    public function getStream(string|array $destination, string $extension): WriteStream
    {
        $destination = is_array($destination) ? $destination : [$destination];

        if (count($destination) === 1) {
            $stream = $this->createStream(reset($destination));

            return static::$streamFactoryClass::make($extension, $stream);
        }

        $outputStreams = [];
        foreach ($destination as $dest) {
            $stream = $this->createStream($dest);

            $outputStreams[] = static::$streamFactoryClass::make($extension, $stream);
        }

        return new ArrayOutputStream($outputStreams);
    }
}
