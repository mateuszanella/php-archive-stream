<?php

namespace PhpArchiveStream\Contracts;

use Generator;

interface ReadStream
{
    public static function open(string $path, int $chunkSize): self;

    public static function fromStream($stream, int $chunkSize): self;

    public static function fromString(string $contents, int $chunkSize): self;

    public function close(): void;

    public function read(): Generator;

    public function size(): int;
}
