<?php

namespace PhpArchiveStream\Writers;

interface Writer
{
    public function addFile(string $path): void;

    public function save(): void;
}
