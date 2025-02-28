<?php

namespace PhpArchiveStream\Writers;

interface Writer
{
    public function addFileFromPath(string $filePath, string $targetPath): void;

    public function addFile(string $fileName, string $fileContents): void;

    public function save(): void;
}
