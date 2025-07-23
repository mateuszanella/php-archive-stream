<?php

namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Concerns\CreatesStreams;
use PhpArchiveStream\Concerns\ParsesPaths;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\Contracts\StreamFactory as StreamFactoryContract;
use PhpArchiveStream\IO\Output\ArrayOutputStream;
use PhpArchiveStream\IO\Output\HttpHeaderWriteStream;

class DestinationManager
{
    use ParsesPaths,
        CreatesStreams;

    /**
     * The class used to create streams.
     */
    protected string $streamFactoryClass;

    /**
     * Create a new DestinationManager instance.
     */
    public function __construct(string $streamFactoryClass)
    {
        $this->useFactory($streamFactoryClass);
    }

    /**
     * Set the stream factory class to be used.
     */
    public function useFactory(string $class): void
    {
        if (! is_subclass_of($class, StreamFactoryContract::class)) {
            throw new InvalidArgumentException("The class must implement " . StreamFactoryContract::class);
        }

        $this->streamFactoryClass = $class;
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
     *
     * @param  string|array<string>  $destination
     * @param  string  $extension
     * @param  array<string, string>  $headers
     *
     * @return WriteStream
     */
    public function getStream(string|array $destination, string $extension, array $headers = []): WriteStream
    {
        if (is_string($destination)) {
            $destination = [$destination];
        }

        $outputStreams = [];
        foreach ($destination as $dest) {
            $stream = $this->createStream($dest);

            $writeStream = $this->streamFactoryClass::make($extension, $stream);

            if ($this->shouldSendHTTPHeaders($dest)) {
                $writeStream = new HttpHeaderWriteStream($writeStream, $headers);
            }

            $outputStreams[] = $writeStream;
        }

        return new ArrayOutputStream($outputStreams);
    }

    public function shouldSendHTTPHeaders(string $destination): bool
    {
        return $destination === 'php://output'
            || $destination === 'php://stdout';
    }
}
