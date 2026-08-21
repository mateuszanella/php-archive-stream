<?php

namespace PhpArchiveStream;

use InvalidArgumentException;
use PhpArchiveStream\Concerns\ParsesPaths;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\IO\Output\ArrayOutputStream;
use PhpArchiveStream\IO\Output\HttpHeaderWriteStream;

/**
 * Resolves destinations into write streams.
 *
 * `DestinationManager` is responsible for the destination side of the
 * library: it parses paths, applies context-specific behavior (such as HTTP
 * headers for web output), and delegates the opening and wrapping of each
 * destination to the {@see StreamManager}.
 */
class DestinationManager
{
    use ParsesPaths;

    /**
     * Create a new DestinationManager instance.
     */
    public function __construct(
        protected StreamManager $streams,
    ) {}

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
            throw new InvalidArgumentException('Could not determine the extension for destinations: '.implode(', ', $destinations));
        }

        $uniqueExtensions = array_unique($perceivedExtensions);

        if (count($uniqueExtensions) > 1) {
            throw new InvalidArgumentException('Multiple different extensions found: '.implode(', ', $destinations));
        }

        return reset($uniqueExtensions);
    }

    /**
     * Create a stream for the given destination and extension.
     *
     * @param  string|array<string>  $destination
     * @param  array<string, string>  $headers
     * @param  array<string, mixed>  $config  Configuration options forwarded to the stream manager.
     */
    public function getStream(string|array $destination, string $extension, array $headers = [], array $config = []): WriteStream
    {
        if (is_string($destination)) {
            $destination = [$destination];
        }

        $outputStreams = [];
        foreach ($destination as $dest) {
            $writeStream = $this->streams->make($extension, $dest, $config);

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
