<?php

declare(strict_types=1);

namespace PhpArchiveStream\Exceptions;

class CouldNotWriteToStreamException extends ArchiveStreamException
{
    public function __construct()
    {
        parent::__construct('Could not write to stream');
    }
}
