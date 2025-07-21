<?php

namespace PhpArchiveStream\IO\Input;

use Generator;
use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

class InputStream implements ReadStream
{
    protected $stream;

    protected int $chunkSize;

    public function __construct($stream, int $chunkSize = 512)
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Argument must be a valid resource');
        }

        $this->chunkSize = $chunkSize;
        $this->stream = $stream;
    }

    public static function open(string $path, int $chunkSize): self
    {
        $stream = fopen($path, 'rb');

        if ($stream === false) {
            throw new CouldNotOpenStreamException($path);
        }

        return new self($stream, $chunkSize);
    }

    public static function fromStream($stream, int $chunkSize): self
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Argument must be a valid resource');
        }

        return new self($stream, $chunkSize);
    }

    public static function fromString(string $contents, int $chunkSize): self
    {
        $stream = fopen('php://memory', 'r+');

        fwrite($stream, $contents);

        rewind($stream);

        return new self($stream, $chunkSize);
    }

    public function close(): void
    {
        fclose($this->stream);

        unset($this->stream);
    }

    public function read(): Generator
    {
        while (! feof($this->stream)) {
            $chunk = fread($this->stream, $this->chunkSize);

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

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            $this->close();
        }
    }
}
