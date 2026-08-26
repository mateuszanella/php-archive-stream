<?php

declare(strict_types=1);

namespace PhpArchiveStream\Exceptions;

class UnsupportedArchiveTypeException extends ArchiveStreamException
{
    public function __construct(string $extension)
    {
        parent::__construct("Unsupported archive type for extension: {$extension}");
    }
}
