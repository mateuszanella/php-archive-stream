<?php

namespace PhpArchiveStream\Support;

use InvalidArgumentException;
use PhpArchiveStream\Concerns\CreatesStreams;
use PhpArchiveStream\Concerns\ParsesPaths;
use PhpArchiveStream\Contracts\IO\WriteStream;
use PhpArchiveStream\IO\Output\ArrayOutputStream;
use PhpArchiveStream\IO\Output\OutputStream;
use PhpArchiveStream\IO\Output\TarGzOutputStream;
use PhpArchiveStream\IO\Output\TarOutputStream;

class DestinationManager
{
    use ParsesPaths,
        CreatesStreams;

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
            throw new InvalidArgumentException("Could not determine the extension for destinations: ".implode(', ', $destinations));
        }

        $uniqueExtensions = array_unique($perceivedExtensions);

        if (count($uniqueExtensions) > 1) {
            throw new InvalidArgumentException("Multiple different extensions found: ".implode(', ', $destinations));
        }

        return reset($uniqueExtensions);
    }

    public function parse(string|array $destination, string $extension): WriteStream
    {
        $destination = is_array($destination) ? $destination : [$destination];

        switch ($extension) {
            case 'zip':
                if (count($destination) === 1) {
                    $stream = $this->createStream(reset($destination));

                    return new OutputStream($stream);
                }

                $outputStreams = [];

                foreach ($destination as $dest) {
                    $stream = $this->createStream($dest);

                    $outputStreams[] = new OutputStream($stream);
                }

                return new ArrayOutputStream($outputStreams);
            case 'tar':
                if (count($destination) === 1) {
                    $stream = $this->createStream(reset($destination));

                    return new TarOutputStream($stream);
                }

                $outputStreams = [];

                foreach ($destination as $dest) {
                    $stream = $this->createStream($dest);

                    $outputStreams[] = new TarOutputStream($stream);
                }

                return new ArrayOutputStream($outputStreams);
            case 'tar.gz':
                if (count($destination) === 1) {
                    $stream = $this->createStream(reset($destination));

                    return new TarGzOutputStream($stream);
                }

                $outputStreams = [];

                foreach ($destination as $dest) {
                    $stream = $this->createStream($dest);

                    $outputStreams[] = new TarGzOutputStream($stream);
                }

                return new ArrayOutputStream($outputStreams);
            default:
                throw new InvalidArgumentException("Unsupported destination type: {$extension}");
        }
    }
}
