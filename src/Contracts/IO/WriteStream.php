<?php

namespace PhpArchiveStream\Contracts\IO;

interface WriteStream
{
    public function close(): void;

    public function write(string $s): int;

    public function getBytesWritten(): int;
}
