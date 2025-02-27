<?php

namespace LaravelFileStream\Writers\Tar;

use Generator;
use LaravelFileStream\Exceptions\CouldNotOpenStreamException;

class InputStream
{
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public static function open(string $path): self
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return new self($stream);
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function read(): Generator
    {
        while (! feof($this->stream)) {
            $chunk = fread($this->stream, 512);

            if ($chunk === false) {
                break;
            }

            yield $chunk;
        }
    }
}
