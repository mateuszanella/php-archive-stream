<?php

namespace PhpArchiveStream\Contracts;

interface WriteStream
{
    public static function open(string $path): self;

    public function close(): void;

    public function write(string $s): void;
}
