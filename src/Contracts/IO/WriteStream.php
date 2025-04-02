<?php

namespace PhpArchiveStream\Contracts\IO;

interface WriteStream
{
    public static function open(string $path): self;

    public function close(): void;

    public function write(string $s): void;

    public function getBytesWritten(): int;
}
