<?php

declare(strict_types=1);

namespace PhpArchiveStream\Exceptions;

class CouldNotOpenStreamException extends ArchiveStreamException
{
    public function __construct(string $path)
    {
        parent::__construct("Could not open file at path: {$path}");
    }
}
