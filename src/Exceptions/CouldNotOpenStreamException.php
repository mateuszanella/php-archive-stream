<?php

namespace PhpArchiveStream\Exceptions;

use Exception;

class CouldNotOpenStreamException extends Exception
{
    public function __construct(string $path)
    {
        parent::__construct("Could not open file at path: {$path}");
    }
}
