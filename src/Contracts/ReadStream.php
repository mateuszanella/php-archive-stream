<?php

namespace PhpArchiveStream\Contracts;

use Generator;

interface ReadStream
{
    public static function open(string $path): self;

    public static function fromStream($stream): self;

    public static function fromString(string $contents): self;

    public function close(): void;

    public function read(): Generator;

    public function size(): int;
}
