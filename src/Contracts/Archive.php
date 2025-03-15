<?php

namespace PhpArchiveStream\Contracts;

interface Archive
{
    public static function create(string $outputPath): self;

    public function addFileFromPath(string $fileName, string $filePath): void;

    public function addFileFromStream(string $fileName, $stream): void;

    public function addFileFromContentString(string $fileName, string $fileContents): void;

    public function finish(): void;
}
