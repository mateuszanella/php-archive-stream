<?php

namespace PhpArchiveStream\Contracts;

interface Writer
{
    public static function create(string $outputPath): self;

    public function addFile(ReadStream $stream, string $fileName): void;

    public function finish(): void;
}
