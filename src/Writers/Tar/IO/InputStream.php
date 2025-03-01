<?php

namespace PhpArchiveStream\Writers\Tar\IO;

use Generator;
use InvalidArgumentException;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

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

    public static function fromStream($stream): self
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Argument must be a valid resource');
        }

        return new self($stream);
    }

    public static function fromString(string $contents): self
    {
        $stream = fopen('php://memory', 'r+');

        fwrite($stream, $contents);

        rewind($stream);

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

    public function size(): int
    {
        $stat = fstat($this->stream);

        return $stat['size'];
    }
}
