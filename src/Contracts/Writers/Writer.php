<?php

namespace PhpArchiveStream\Contracts\Writers;

use PhpArchiveStream\Contracts\IO\ReadStream;

interface Writer
{
    public function addFile(ReadStream $stream, string $fileName): void;

    public function finish(): void;
}
