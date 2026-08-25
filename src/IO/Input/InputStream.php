<?php

declare(strict_types=1);

namespace PhpArchiveStream\IO\Input;

use Generator;
use InvalidArgumentException;
use PhpArchiveStream\Contracts\IO\ReadStream;
use PhpArchiveStream\Exceptions\CouldNotOpenStreamException;

/**
 * @internal
 */
class InputStream implements ReadStream
{
    /**
     * @var resource|null
     */
    protected $stream;

    protected int $chunkSize;

    /**
     * @param  resource  $stream  A valid, readable stream resource.
     */
    public function __construct($stream, int $chunkSize = 512)
    {
        if (! is_resource($stream)) {
            throw new InvalidArgumentException('Argument must be a valid resource');
        }

        $this->chunkSize = $chunkSize;
        $this->stream = $stream;
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            $this->close();
        }
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
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }

        $this->stream = null;
    }

    public function read(): Generator
    {
        while (is_resource($this->stream) && ! feof($this->stream)) {
            $chunk = fread($this->stream, $this->chunkSize);

            if ($chunk === false) {
                break;
            }

            yield $chunk;
        }
    }

    public function size(): int
    {
        if (! is_resource($this->stream)) {
            return 0;
        }

        $stat = fstat($this->stream);

        if ($stat === false) {
            return 0;
        }

        return $stat['size'];
    }
}
