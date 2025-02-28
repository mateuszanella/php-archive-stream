<?php

namespace PhpArchiveStream\Writers;

interface Writer
{
    public function addFileFromPath(string $fileName, string $filePath): void;

    public function addFileFromStream(string $fileName, $stream): void;

    public function addFileFromContentString(string $fileName, string $fileContents): void;

    public function save(): void;
}
